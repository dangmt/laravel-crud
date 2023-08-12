<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayPal\Api\Amount;
use PayPal\Api\ItemList;
use PayPal\Api\Item;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPal\Exception\PayPalConnectionException;

class PaypalController extends Controller
{
    public function payment(Request $request)
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                config('services.paypal.client_id'),
                config('services.paypal.secret')
            )
        );

        $apiContext->setConfig(config('services.paypal.settings'));

        // Retrieve all cart items from the database
        $cartItems = CartItem::all();
        $orderAmount = $this->calculateOrderTotal($cartItems);

        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $itemList = new ItemList();

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;
                $item = new Item();
                $item->setName($product->name)
                    ->setCurrency('USD')
                    ->setQuantity($cartItem->quantity)
                    ->setPrice($product->price);
                $itemList->addItem($item);
            }

            $amount = new Amount();
            $amount->setTotal($orderAmount)
                ->setCurrency('USD');

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription('Purchase from Your Store');

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(route('paypal.complete'))
                ->setCancelUrl(route('paypal.cancel'));

            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setTransactions([$transaction])
                ->setRedirectUrls($redirectUrls);

            $payment->create($apiContext);
            // print_r($payment->create($apiContext)->toJSON());
            return response()->json(['approval_link' => $payment->getApprovalLink()]);
            // return response()->json($payment->getApprovalLink());

            //return redirect()->away($payment->getApprovalLink());
        } catch (PayPalConnectionException $ex) {
            return back()->withErrors(['error' => 'PayPal API error']);
        }
    }

    private function calculateOrderTotal($cartItems)
    {
        $orderAmount = 0;

        foreach ($cartItems as $cartItem) {
            $orderAmount += $cartItem->product->price * $cartItem->quantity;
        }

        return $orderAmount;
    }
}
