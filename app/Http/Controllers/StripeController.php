<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
  
    public function payment(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve all cart items from the database
        $cartItems = CartItem::all();
        // print_r($this->calculateOrderTotal($cartItems));
        $orderAmount = $this->calculateOrderTotal($cartItems);

        $paymentIntent = PaymentIntent::create([
            'amount' => $orderAmount * 100,
            'currency' => 'usd',
        ]);

        return response()->json(['client_secret' => $paymentIntent->client_secret]);
        // return response()->json(["{\"client_secret\": \"" + $paymentIntent->client_secret + "\"}"]);
    }

    private function calculateOrderTotal($cartItems)
    {
        $orderAmount = 0;

        foreach ($cartItems as $cartItem) {
            // Assuming each cart item corresponds to a product with a 'price' attribute
            $orderAmount += $cartItem->product->price * $cartItem->quantity;
        }

        return $orderAmount;
    }
}
