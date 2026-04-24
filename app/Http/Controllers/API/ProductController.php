<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all();
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'tag' => 'required',
            'description' => 'nullable|string'
        ]);

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'product' => $product
        ], 201);
    }
}
