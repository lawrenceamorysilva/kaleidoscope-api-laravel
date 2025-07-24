<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ShippingController extends Controller
{
    /**
     * Get shipping cost options based on postcode, suburb, and weight.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShippingCost(Request $request)
    {
        // Validate incoming request inputs
        $request->validate([
            'postcode' => 'required|string',
            'suburb' => 'required|string',
            'weight' => 'required|numeric|min:0',
        ]);

        // Normalize input
        $postcode = trim($request->input('postcode'));
        $suburb = strtoupper(trim($request->input('suburb')));
        $weight = ceil((float) $request->input('weight')); // round up

        // Use a normalized cache key
        $cacheKey = "shipping_costs:" . strtolower($suburb) . ":$postcode:$weight";

        $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($postcode, $suburb, $weight) {
            // Validation: Confirm suburb/postcode combo exists
            $validPair = DB::table('shipping_costs')
                ->where('postcode', $postcode)
                ->whereRaw('UPPER(suburb) = ?', [$suburb])
                ->exists();

            if (!$validPair) {
                abort(response()->json([
                    'error' => 'Invalid suburb/postcode combination.',
                ], 422));
            }

            // Lookup shipping options
            $costs = DB::table('shipping_costs')
                ->where('postcode', $postcode)
                ->whereRaw('UPPER(suburb) = ?', [$suburb])
                ->where('weight_kg', $weight)
                ->select('courier', 'suburb', 'cost_aud as cost')
                ->get()
                ->map(function ($item) {
                    return [
                        'courier' => $item->courier,
                        'suburb' => $item->suburb,
                        'cost' => number_format((float) $item->cost, 2, '.', ''),
                    ];
                })
                ->sortBy('cost')
                ->values()
                ->toArray();

            if (empty($costs)) {
                abort(response()->json([
                    'error' => "We couldn’t find a shipping option for $suburb $postcode with total weight of $weight kg. Please adjust the order or contact support.",
                ], 404));
            }


            return [
                'postcode' => $postcode,
                'suburb' => $suburb,
                'weight' => $weight,
                'options' => $costs,
                'default' => $costs[0],
            ];
        });

        return response()->json($data);
    }


}
