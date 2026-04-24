<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
     public function index()
    {
       return Order::with('items.product')->latest()->get();

    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255'

        ]);

        if (!isset($validated['items']) || count($validated['items']) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 422);
        }

        $order = Order::create([
            'customer_name' => $validated['customer_name'],
            'total_amount' => 0,
            'status' => 'completed'
        ]);

        $total = 0;

        foreach ($validated['items'] as $item) {

            $product = Product::findOrFail($item['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock for {$product->name}'
                ], 400);
            }

            // reduce stock
            $product->stock -= $item['quantity'];
            $product->save();

            // create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            $total += $product->price * $item['quantity'];
        }

        $order->update([
            'total_amount' => $total
        ]);

        return response()->json([
            'success' => true,
            'orderId' => $order->id
        ], 200);
    }
}
