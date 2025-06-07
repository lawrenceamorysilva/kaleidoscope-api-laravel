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

        // Retrieve inputs with trimming and formatting
        $postcode = $request->input('postcode');
        $suburb = strtoupper(trim($request->input('suburb')));
        $weight = ceil((float) $request->input('weight'));

        // Cache key to store/retrieve cached results for 120 minutes
        $cacheKey = "shipping_costs:$postcode:$suburb:$weight";

        // Use cache remember to either fetch cached or execute the DB query
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

        // Return JSON response
        return response()->json($data);
    }
}
