<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;

class CartItemController extends Controller
{



    public function store(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        $product = Product::find($productId);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // Check if the product is already in the cart
        $cartItem = CartItem::where('product_id', $productId)->first();

        if ($cartItem) {
            // Update the quantity of the existing cart item
            $cartItem->quantity += $quantity;
            $cartItem->save();
            return response()->json(['message' => 'Product quantity updated in cart']);
        } else {
            // Create a new cart item
            $cartItem = new CartItem([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
            $cartItem->save();
            return response()->json(['message' => 'Product added to cart']);
        }
    }

    public function show($cartItemId)
    {
        $cartItem = CartItem::with('product')->find($cartItemId);

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        return response()->json($cartItem);
    }
    public function update(Request $request, $id)
    {
        // Validate incoming request data
        $this->validate($request, [
            'quantity' => 'required|integer|min:1',
        ]);

        // Logic to update cart item quantity
        $cartItem = CartItem::findOrFail($id);
        $cartItem->update(['quantity' => $request->input('quantity')]);

        return response()->json(['message' => 'Cart item updated', 'cart_item' => $cartItem]);
    }

    public function destroy($id)
    {
        // Logic to remove item from the cart
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return response()->json(['message' => 'Cart item removed']);
    }

    public function index()
    {
        // Logic to retrieve and return cart items with product information
        $cartItems = CartItem::with('product')->get();

        return response()->json(['cart_items' => $cartItems]);
    }
}
