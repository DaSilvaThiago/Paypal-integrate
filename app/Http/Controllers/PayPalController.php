<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    public function paypal(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
              "return_url" => route('success'),
              "cancel_url" => route('cancel'),
            ],
            "purchase_units" => [
              [
                "amount" => [
                  "currency_code" => "USD",
                  "value" => $request->price
                ]
              ]
            ]
        ]);
        // dd($order);
        if(isset($order['id']) && $order['id'] != null){
            foreach($order['links'] as $link) {
                if($link['rel'] === 'approve') {
                    session()->put('product_name', $request->product_name);
                    session()->put('quantity', $request->quantity);
                    return redirect()->away($link['href']);
                }
            }
        }
    }

    public function success(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $order = $provider->capturePaymentOrder($request->token);
        
        if(isset($order['status']) && $order['status'] == 'COMPLETED'){
            
            $payment = new Payment;
            $payment->payment_Id = $order['id'];
            $payment->product_name = session()->get('product_name');
            $payment->quantity = session()->get('quantity');
            $payment->amount = $order['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
            $payment->currency = $order['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
            $payment->payer_name = $order['payer']['name']['given_name'];
            $payment->payer_email = $order['payer']['email_address'];
            $payment->payment_status = $order['status'];
            $payment->payment_method = "PayPal";
            $payment->save();
            
            return "Payment has been made Successfully!";

            unset($_SESSION['product_name']);
            unset($_SESSION['quantity']);

        }else{
            return redirect()->route('cancel');
        }
    }

    public function cancel()
    {
        return "Payment has been Cancelled!";
    }
}