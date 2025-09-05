<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NetoProduct;

class NetoProductController extends Controller
{
    // used by admin portal drop and nondrop pages || retailer portal home
    // Existing getNetoProducts(Request $request)
    public function index(Request $request)
    {
        $sku = $request->query('sku');
        $dropship = $request->query('dropship');
        $retailer = $request->boolean('retailer', false); // distinguishes portals

        if ($sku) {
            // Single product (always return full data for admin, slimmed for retailer)
            $query = NetoProduct::query();

            if ($retailer) {
                //$query->select(['sku','name','brand','stock_status','dropship_price','images']);
                $query->select(['name','images']);
            }

            $product = $query
                ->when($dropship === 'Yes' || $dropship === 'No', fn($q) => $q->where('dropship', $dropship))
                ->where('sku', $sku)
                ->first();

            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            return response()->json([
                'version' => Cache::get('neto_products_version', 1),
                'product' => $product
            ]);
        }

        // Cache key should vary depending on dropship filter and portal
        $cacheKey = 'neto_products_all_' . ($dropship ?? 'all') . '_retailer_' . ($retailer ? '1' : '0');

        $products = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($dropship, $retailer) {
            $query = NetoProduct::query();

            if ($retailer) {
                // Retailer = slimmed down list
                $query->select([
                    'sku','name','brand','stock_status','dropship_price','updated_at'
                ]);
            } else {
                // Admin = full dataset
                $query->select([
                    'sku',
                    'name',
                    'brand',
                    'stock_status',
                    'dropship',
                    'dropship_price',
                    'surcharge',
                    'qty',
                    'qty_buffer',
                    'shipping_weight',
                    'shipping_length',
                    'shipping_width',
                    'shipping_height',
                    'updated_at'
                ]);
            }

            if ($dropship === 'Yes' || $dropship === 'No') {
                $query->where('dropship', $dropship);
            }

            return $query->get();
        });

        return response()->json([
            'version' => Cache::get('neto_products_version', 1),
            'products' => $products,
        ]);
    }








    //used by admin portal
    public function getBySku($sku)
    {
        $product = NetoProduct::where('sku', $sku)
            ->where('dropship', 'Yes')
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            return response()->json(['error' => 'SKU not found or not available for dropshipping'], 404);
        }

        $availableQty = max(0, ($product->qty ?? 0) - ($product->qty_buffer ?? 0));

        return response()->json([
            'sku' => $product->sku,
            'name' => $product->name,
            'brand' => $product->brand,
            'price' => number_format($product->dropship_price ?? 0, 2, '.', ''),
            'surcharge' => number_format($product->surcharge ?? 0, 2, '.', ''),
            'qty' => $availableQty,
            'netoId' => $product->neto_id,
            'stockStatus' => $product->stock_status,
            'shippingWeight' => $product->shipping_weight,
        ]);
    }


    //used by retailer portal
    public function lookupSkus(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|max:20',
            'items.*.sku' => 'required|string',
            'items.*.qty' => 'nullable|integer|min:1'
        ]);

        $skus = collect($validated['items'])->pluck('sku')->map(fn($s) => trim($s))->unique()->all();

        // ✅ Load cache (flat array, not a Collection)
        $allProducts = cache('neto_products_cache', []);

        // ✅ Detect stale cache based on missing fields
        $needsRefresh = empty($allProducts) || collect($allProducts)->contains(function ($p) {
                return !isset($p['dropship']);
            });

        if ($needsRefresh) {
            \Log::warning('♻️ neto_products_cache is missing or stale. Rebuilding from DB...');

            $allProducts = NetoProduct::query()
                ->where('is_active', true)
                ->get([
                    'sku', 'name', 'dropship_price', 'shipping_weight',
                    'qty', 'qty_buffer', 'dropship'
                ])
                ->mapWithKeys(function ($p) {
                    return [$p->sku => [
                        'name' => $p->name,
                        'dropship_price' => $p->dropship_price,
                        'shipping_weight' => $p->shipping_weight,
                        'qty_available' => $p->qty - $p->qty_buffer,
                        'dropship' => $p->dropship,
                    ]];
                })
                ->toArray();

            Cache::put('neto_products_cache', $allProducts, now()->addHours(6));

            \Log::info('✅ neto_products_cache rebuilt. Count: ' . count($allProducts));
            \Log::info('🧪 Sample SKUs: ' . implode(', ', array_slice(array_keys($allProducts), 0, 5)));
        }

        // ✅ Continue SKU lookup using cache
        $results = [];

        foreach ($validated['items'] as $item) {
            $sku = trim($item['sku']);
            $qty = $item['qty'] ?? 1;

            $product = $allProducts[$sku] ?? null;

            if (!$product) {
                $results[] = [
                    'sku' => $sku,
                    'error' => 'Cannot find SKU, please check and try again'
                ];
                continue;
            }

            $availableQty = $product['qty_available'];
            $inStock = $availableQty >= $qty;

            $results[] = [
                'sku' => $sku,
                'name' => $product['name'],
                'dropship_price' => $product['dropship_price'],
                'shipping_weight' => $product['shipping_weight'],
                'qty_available' => $availableQty,
                'in_stock' => $inStock,
                'dropship' => $product['dropship'] ?? null,
                'error' => $inStock ? null : 'Product is not in stock'
            ];
        }

        return response()->json($results);
    }









}
