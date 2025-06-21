<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NetoProduct;

class NetoProductController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = NetoProduct::query();

    //     // Optional Filters
    //     if ($search = $request->input('search')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('sku', 'like', "%{$search}%")
    //               ->orWhere('name', 'like', "%{$search}%")
    //               ->orWhere('brand', 'like', "%{$search}%");
    //         });
    //     }

    //     if ($status = $request->input('status')) {
    //         $query->where('status', $status);
    //     }

    //     // Optional Sorting
    //     $sortBy = $request->input('sort_by', 'created_at');
    //     $sortDir = $request->input('sort_dir', 'desc');

    //     $query->orderBy($sortBy, $sortDir);

    //     // Paginate results
    //     $products = $query->paginate($request->input('per_page', 20));

    //     return response()->json($products);
    // }

    public function index(Request $request)
    {
        return NetoProduct::all(); // basic test response
    }
    
}
