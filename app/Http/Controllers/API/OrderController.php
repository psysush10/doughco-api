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
        $items = $request->items;
        $customerName = $request->customer_name;

        if (!$items || count($items) === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No items in order'
            ], 400);
        }

        $order = Order::create([
            'customer_name' => $customerName,
            'total_amount' => 0,
            'status' => 'completed'
        ]);

        $total = 0;

        foreach ($items as $item) {

            $product = Product::find($item['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Out of stock'
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
