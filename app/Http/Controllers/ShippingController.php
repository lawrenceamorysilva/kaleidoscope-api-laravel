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

        // Retrieve and sanitize inputs
        $postcode = trim($request->input('postcode'));
        $suburb = strtoupper(trim($request->input('suburb')));
        $weight = ceil((float) $request->input('weight'));

        // Validation: Check suburb exists
        $suburbExists = DB::table('shipping_costs')
            ->whereRaw('UPPER(suburb) = ?', [$suburb])
            ->exists();

        if (!$suburbExists) {
            return response()->json(['error' => 'Suburb does not exist.'], 422);
        }

        // Validation: Check postcode exists
        $postcodeExists = DB::table('shipping_costs')
            ->where('postcode', $postcode)
            ->exists();

        if (!$postcodeExists) {
            return response()->json(['error' => 'Postcode does not exist.'], 422);
        }

        // Validation: Check suburb belongs to postcode
        $validPair = DB::table('shipping_costs')
            ->where('postcode', $postcode)
            ->whereRaw('UPPER(suburb) = ?', [$suburb])
            ->exists();

        if (!$validPair) {
            return response()->json(['error' => 'Suburb does not belong to the specified postcode.'], 422);
        }

        // Proceed to cost calculation (cached)
        $cacheKey = "shipping_costs:$postcode:$suburb:$weight";

        $data = Cache::remember($cacheKey, now()->addMinutes(120), function () use ($postcode, $suburb, $weight) {
            $costs = DB::table('shipping_costs')
                ->where('postcode', $postcode)
                ->whereRaw('UPPER(suburb) = ?', [$suburb])
                ->where('weight_kg', $weight)
                ->select('courier', 'suburb', 'cost_aud as cost')
                ->get()
                ->map(function ($item) use ($suburb) {
                    return [
                        'courier' => $item->courier,
                        'suburb' => $suburb,
                        'cost' => number_format((float) $item->cost, 2, '.', ''),
                    ];
                })
                ->sortBy('cost')
                ->values()
                ->toArray();

            if (count($costs) === 0) {
                abort(response()->json([
                    'error' => "No shipping costs found for postcode $postcode, suburb $suburb, and weight $weight kg",
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
