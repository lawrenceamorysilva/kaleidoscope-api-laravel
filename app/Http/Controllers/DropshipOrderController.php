<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\DropshipOrder;
use App\Models\DropshipOrderItem;

class DropshipOrderController extends Controller
{

    public function show($id)
    {
        $order = DropshipOrder::with('items')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
                'id' => $id
            ], 404);
        }

        return response()->json([
            'order' => $order
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


    public function update(Request $request, $id)
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
            $order = DropshipOrder::findOrFail($id);

            // Update order fields
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
            ]);

            // Replace items: delete existing and recreate
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

            return response()->json(['message' => 'Dropship order updated successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('❌ Failed to update dropship order: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to update dropship order'], 500);
        }
    }



}
