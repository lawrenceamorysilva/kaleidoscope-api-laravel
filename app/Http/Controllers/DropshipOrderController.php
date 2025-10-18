<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DropshipOrder;
use App\Models\DropshipOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DropshipOrderController extends Controller
{

    public function openSummary()
    {
        $userId = Auth::id();

        // Build the query
        $query = DropshipOrder::where('user_id', $userId)
            ->where('status', 'open')
            ->select(
                'id',
                'first_name',
                'last_name',
                'product_total',
                'dropship_fee',
                'min_order_fee',
                'shipping_total',
                'grand_total'
            )
            ->orderBy('created_at', 'desc');

        // Log the SQL with bindings
        \Log::info('KIWI openSummary query: ' . $query->toSql(), $query->getBindings());

        // Execute and transform
        $orders = $query->get()->map(function ($order) {
            return [
                'id'            => $order->id,
                'name'          => $order->first_name . ' ' . $order->last_name,
                'product_total' => $order->product_total,
                'dropship_fee'  => $order->dropship_fee,
                'min_order_fee' => $order->min_order_fee,
                'shipping_total'=> $order->shipping_total,
                'grand_total'   => $order->grand_total,
            ];
        });

        return response()->json([
            'orders' => $orders
        ]);
    }



    public function show($id)
    {
        $userId = Auth::id();

        $order = DropshipOrder::with('items')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found.',
                'id' => $id,
            ], 404);
        }

        return response()->json([
            'order' => $order,
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'business_name' => 'nullable|string',
            'shipping_address_line1' => 'required|string',
            'shipping_address_line2' => 'nullable|string',
            'suburb' => 'required|string',
            'state' => 'required|string',
            'postcode' => 'required|string',
            'phone' => 'nullable|string',
            'authority_to_leave' => 'nullable|boolean',
            'product_total' => 'required|numeric',
            'shipping_total' => 'required|numeric',
            'dropship_fee' => 'required|numeric',
            'min_order_fee' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'selected_courier' => 'nullable|string',
            'available_shipping_options' => 'nullable|array',
            'items' => 'required|array|min:1|max:20',
            'items.*.sku' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.name' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            $order = DropshipOrder::create([
                'user_id' => auth()->id(),
                'po_number' => $validated['po_number'],
                'delivery_instructions' => $validated['delivery_instructions'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'business_name' => $validated['business_name'],
                'shipping_address_line1' => $validated['shipping_address_line1'],
                'shipping_address_line2' => $validated['shipping_address_line2'],
                'suburb' => $validated['suburb'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'phone' => $validated['phone'],
                'authority_to_leave' => $validated['authority_to_leave'],
                'product_total' => $validated['product_total'],
                'shipping_total' => $validated['shipping_total'],
                'dropship_fee' => $validated['dropship_fee'],
                'min_order_fee' => $validated['min_order_fee'],
                'grand_total' => $validated['grand_total'],
                'selected_courier' => $validated['selected_courier'],
                'available_shipping_options' => $request->input('available_shipping_options'),
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'sku' => $item['sku'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'name' => $item['name'] ?? null
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Dropship order saved successfully',
                'order_id' => $order->id
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Failed to save dropship order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to save dropship order'
            ], 500);
        }
    }


    /* used to simply update status OR to literally update every part of the dropship order*/
    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $order = DropshipOrder::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // ✅ Case 1: Status-only update
        if ($request->has('status') && count($request->all()) === 1) {
            $validated = $request->validate([
                'status' => 'required|string|in:open,for_shipping,fulfilled,canceled,removed',
            ]);

            $order->update(['status' => $validated['status']]);

            return response()->json([
                'message' => "Order status updated to {$validated['status']}",
                'order_id' => $order->id,
            ]);
        }

        // ✅ Case 2: Full update
        $validated = $request->validate([
            'po_number' => 'nullable|string',
            'delivery_instructions' => 'nullable|string',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'business_name' => 'nullable|string',
            'shipping_address_line1' => 'required|string',
            'shipping_address_line2' => 'nullable|string',
            'suburb' => 'required|string',
            'state' => 'required|string',
            'postcode' => 'required|string',
            'phone' => 'nullable|string',
            'authority_to_leave' => 'nullable|boolean',
            'product_total' => 'required|numeric',
            'shipping_total' => 'required|numeric',
            'dropship_fee' => 'required|numeric',
            'min_order_fee' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'selected_courier' => 'nullable|string',
            'available_shipping_options' => 'nullable|array',
            'items' => 'required|array|min:1|max:20',
            'items.*.sku' => 'required|string',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.name' => 'required|string',
            'status' => 'nullable|string|in:open,for_shipping,fulfilled,canceled,removed',
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'po_number' => $validated['po_number'] ?? null,
                'delivery_instructions' => $validated['delivery_instructions'] ?? null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'business_name' => $validated['business_name'] ?? null,
                'shipping_address_line1' => $validated['shipping_address_line1'],
                'shipping_address_line2' => $validated['shipping_address_line2'] ?? null,
                'suburb' => $validated['suburb'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'phone' => $validated['phone'] ?? null,
                'authority_to_leave' => $validated['authority_to_leave'] ?? false,
                'product_total' => $validated['product_total'],
                'shipping_total' => $validated['shipping_total'],
                'dropship_fee' => $validated['dropship_fee'],
                'min_order_fee' => $validated['min_order_fee'],
                'grand_total' => $validated['grand_total'],
                'selected_courier' => $validated['selected_courier'] ?? null,
                'available_shipping_options' => $request->input('available_shipping_options'),
                'status' => $validated['status'] ?? $order->status,
            ]);

            // Replace items safely
            $order->items()->delete();
            foreach ($validated['items'] as $item) {
                $order->items()->create([
                    'sku' => $item['sku'],
                    'name' => $item['name'] ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();
            return response()->json([
                'message' => 'Dropship order updated successfully',
                'order_id' => $order->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Failed to update dropship order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update dropship order'], 500);
        }
    }





    public function bulkUpdateStatus(Request $request)
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'order_ids'   => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:dropship_orders,id',
            'status'      => 'required|string|in:open,for_shipping,fulfilled,canceled',
        ]);

        DB::beginTransaction();
        try {
            // Only update the orders belonging to this user
            $updatedCount = DropshipOrder::whereIn('id', $validated['order_ids'])
                ->where('user_id', $userId)
                ->update(['status' => $validated['status']]);

            DB::commit();

            if ($updatedCount === 0) {
                return response()->json([
                    'message' => 'No orders updated (none belonged to you)',
                ], 403);
            }

            return response()->json([
                'message' => 'Orders updated successfully',
                'updated' => $updatedCount,
                'status'  => $validated['status'],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Bulk update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update orders'], 500);
        }
    }



    public function history(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $status = $request->query('status'); // optional filter

        $query = DropshipOrder::where('user_id', $userId)
            ->whereIn('status', ['for_shipping', 'fulfilled', 'canceled'])
            ->select(
                'id',
                'first_name',
                'last_name',
                'status',
                'product_total',
                'dropship_fee',
                'min_order_fee',
                'shipping_total',
                'grand_total',
                'updated_at',
                'created_at',
            )
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->get()->map(function ($order) {
            return [
                'id'            => $order->id,
                'name'          => $order->first_name . ' ' . $order->last_name,
                'status'        => $order->status,
                'product_total' => $order->product_total,
                'dropship_fee'  => $order->dropship_fee,
                'min_order_fee' => $order->min_order_fee,
                'shipping_total'=> $order->shipping_total,
                'grand_total'   => $order->grand_total,
                'updated_at'    => $order->updated_at->toDateTimeString(),
                'created_at'    => $order->created_at->toDateTimeString(),
            ];
        });

        return response()->json([
            'orders' => $orders
        ]);
    }




    //ADMIN PORTAL STUFF BELOW:::
    public function adminExportHistory(Request $request): JsonResponse
    {
        $orders = \App\Models\DropshipOrderFilename::with([
            'adminUser:id,name,email',
            'orders.items',
            'orders.user:id,username',
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($batch) {
                return [
                    'id'           => $batch->id,
                    'filename'     => $batch->filename,
                    'exported_at'  => $batch->created_at,
                    'download_url' => url("/storage/exports/{$batch->filename}"),
                    'exported_by'  => optional($batch->adminUser)->name,
                    'exported_by_email' => optional($batch->adminUser)->email,
                    'orders'       => $batch->orders->map(function ($order) {
                        return [
                            'id'            => $order->id,
                            'username'      => optional($order->user)->username,
                            'po_number'     => $order->po_number,
                            'status'        => $order->status,
                            'grand_total'   => $order->grand_total,
                            'shipping_total'=> $order->shipping_total,
                            'created_at'    => $order->created_at,
                            'items'         => $order->items->map(fn ($item) => [
                                'sku'   => $item->sku,
                                'name'  => $item->name,
                                'qty'   => $item->qty,
                                'price' => $item->price,
                            ])->values(),
                        ];
                    })->values(),
                ];
            })->values();

        return response()->json(['data' => $orders]);
    }



    public function adminIndex(Request $request): JsonResponse
    {

        // Log the Authorization header
        /*\Log::info('KIWI AdminIndex called', [
            'authorization' => $request->header('Authorization'),
            'token' => $request->bearerToken(),
            'guard_user' => auth('admin')->user(),
        ]);

        if (!auth('admin')->check()) {
            return response()->json(['error' => 'Unauthenticated admin!'], 401);
        }*/


        $cacheKey = 'admin_export_orders';

        $orders = cache()->remember($cacheKey, now()->addSeconds(20), function () {
            return DropshipOrder::select([
                'id', 'user_id', 'po_number', 'delivery_instructions', 'authority_to_leave',
                'first_name', 'last_name', 'business_name', 'shipping_address_line1',
                'shipping_address_line2', 'suburb', 'state', 'postcode', 'phone',
                'product_total', 'dropship_fee', 'min_order_fee', 'shipping_total',
                'grand_total', 'selected_courier', 'created_at'
            ])
                ->with([
                    'items:id,dropship_order_id,sku,name,qty,price',
                    'user:id,username'
                ])
                ->where('status', 'for_shipping')
                ->whereNull('dropship_order_filename_id')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($order) {
                    $data = $order->toArray();
                    $data['username'] = optional($order->user)->username;
                    unset($data['user']);
                    return $data;
                })
                ->values();
        });

        return response()->json(['orders' => $orders]);
    }



    public function exportCsv(Request $request)
    {
        $orders = $request->input('orders', []);

        if (empty($orders)) {
            return response()->json(['message' => 'No orders provided'], 400);
        }


        $now = \Carbon\Carbon::now();
        $filename = 'kaldropshipping_orders_' . $now->format('dmY_His') . '.csv';
        $exportDir = storage_path('app/public/exports');

        // Ensure the exports directory exists
        if (!file_exists($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $filePath = $exportDir . "/{$filename}";
        $file = fopen($filePath, 'w');

        // CSV header
        $header = [
            'Username','Purchase Order ID','Customer Instructions','Ship First Name','Ship Last Name',
            'Ship Company','Ship Address Line 1','Ship Address Line 2','Ship City','Ship State',
            'Ship Post Code','Ship Phone','Shipping Cost','Order Line SKU','Order Line Qty',
            'Shipping Method','Order Line Unit Price','Signature Required','Order Type'
        ];
        fputcsv($file, $header);

        foreach ($orders as $do) {
            if (!isset($do['items']) || !count($do['items'])) continue;

            // Purchase Order ID (concatenation as per spec, no username)
            $poId = ($do['po_number'] ?? '') . ' '
                . trim(($do['last_name'] ?? '') . ' ' . ($do['business_name'] ?? '')) . ' '
                . $now->format('YmdHi') . 'DO'
                . ($do['id'] ?? '');

            // Customer Instructions + Delivery Instructions Formatting
            $deliveryInstructions = trim($do['delivery_instructions'] ?? '');
            $authToLeave = !empty($do['authority_to_leave']) && $do['authority_to_leave'] == 1;

            if ($authToLeave && $deliveryInstructions !== '') {
                $customerInstructions = 'AUTHORITY TO LEAVE | ' . $deliveryInstructions;
            } elseif ($authToLeave) {
                $customerInstructions = 'AUTHORITY TO LEAVE';
            } else {
                $customerInstructions = $deliveryInstructions;
            }


            // Shipping Addresses
            $shipAddress1 = $do['shipping_address_line1'] ?? '';
            $shipAddress2 = $do['shipping_address_line2'] ?? '';

            foreach ($do['items'] as $doi) {
                fputcsv($file, [
                    $do['username'] ?? '',
                    $poId,
                    $customerInstructions,
                    $do['first_name'] ?? '',
                    $do['last_name'] ?? '',
                    $do['business_name'] ?? '',
                    $shipAddress1,
                    $shipAddress2,
                    $do['suburb'] ?? '',
                    $do['state'] ?? '',
                    $do['postcode'] ?? '',
                    $do['phone'] ?? '',
                    $do['shipping_total'] ?? 0,
                    $doi['sku'] ?? '',
                    $doi['qty'] ?? 1,
                    $do['selected_courier'] ?? '',
                    $doi['price'] ?? 0,
                    'No',
                    'Dropshipping'
                ]);
            }

            // MIN-ORDER row
            if (!empty($do['min_order_fee']) && $do['min_order_fee'] > 0) {
                fputcsv($file, [
                    $do['username'] ?? '',
                    $poId,
                    $customerInstructions,
                    $do['first_name'] ?? '',
                    $do['last_name'] ?? '',
                    $do['business_name'] ?? '',
                    $shipAddress1,
                    $shipAddress2,
                    $do['suburb'] ?? '',
                    $do['state'] ?? '',
                    $do['postcode'] ?? '',
                    $do['phone'] ?? '',
                    $do['shipping_total'] ?? 0,
                    'MIN-ORDER',
                    1,
                    $do['selected_courier'] ?? '',
                    $do['min_order_fee'],
                    'No',
                    'Dropshipping'
                ]);
            }

            // ADROP row
            fputcsv($file, [
                $do['username'] ?? '',
                $poId,
                $customerInstructions,
                $do['first_name'] ?? '',
                $do['last_name'] ?? '',
                $do['business_name'] ?? '',
                $shipAddress1,
                $shipAddress2,
                $do['suburb'] ?? '',
                $do['state'] ?? '',
                $do['postcode'] ?? '',
                $do['phone'] ?? '',
                $do['shipping_total'] ?? 0,
                'ADROP',
                1,
                $do['selected_courier'] ?? '',
                11,
                'No',
                'Dropshipping'
            ]);
        }


        fclose($file);

        // insert into dropship_order_filename
        $dofId = \DB::table('dropship_order_filename')->insertGetId([
            'filename' => $filename,
            'created_at' => $now,
            'dl_counter' => 1,
            'admin_users_id' => \Auth::id(),
            'dl_date' => $now
        ]);

        // update dropship_orders
        $orderIds = array_column($orders, 'id');
        \DB::table('dropship_orders')
            ->whereIn('id', $orderIds)
            ->update([
                'dropship_order_filename_id' => $dofId,
                'updated_at' => $now
            ]);

        // Instead of immediate download, return URL for Angular
        $downloadUrl = asset("storage/exports/{$filename}");

        return response()->json([
            'message' => 'CSV ready',
            'downloadUrl' => $downloadUrl
        ]);
    }








}
