<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NetoProduct;

class NetoProductController extends Controller
{
    //used by admin portal
    public function index(Request $request)
    {
        return Cache::remember('neto_products_all', now()->addMinutes(10), function () {
            return NetoProduct::select([
                'sku',
                'name',
                'brand',
                'stock_status',
                'dropship_price',
                'surcharge',
                'qty',
                'qty_buffer',
                'shipping_weight',
                'shipping_length',
                'shipping_width',
                'shipping_height',
                'updated_at',
            ])
                ->where('dropship', 'Yes')
                ->get();
        });

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

        // ✅ Load flat array from cache (not a Collection)
        $allProducts = cache('neto_products_cache', []);

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

            // ✅ Use qty_available directly from cache
            $availableQty = $product['qty_available'];
            $inStock = $availableQty >= $qty;

            $results[] = [
                'sku' => $sku,
                'name' => $product['name'],
                'dropship_price' => $product['dropship_price'],
                'shipping_weight' => $product['shipping_weight'],
                'qty_available' => $availableQty,
                'in_stock' => $inStock,
                'error' => $inStock ? null : 'Product is not in stock'
            ];
        }

        return response()->json($results);
    }








}
