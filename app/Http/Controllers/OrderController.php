<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // Create a new order
        $order = new Order();
        $order->save();

        // Move cart items to order items
        $cartItems = CartItem::all();

        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ]);
            $orderItem->save();

            $cartItem->delete();
        }

        return response()->json(['message' => 'Order created successfully']);
    }

    public function show($orderId)
    {
        $order = Order::with('orderItems.product')->find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }
    public function update(Request $request, $orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Update order properties if needed
        // Example: $order->status = $request->input('status');
        // $order->save();

        return response()->json(['message' => 'Order updated successfully']);
    }

    public function destroy($orderId)
    {
        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Delete associated order items
        //OrderItem::where('order_id', $orderId)->delete();

        // Delete the order
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
    public function index()
    {
        $orders = Order::with('orderItems.product')->get();
        return response()->json(["orders:" => $orders]);
    }
}
