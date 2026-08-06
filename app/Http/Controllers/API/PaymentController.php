<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function pay(Request $request, $orderId)
    {
          
        try {

            //  Find order belonging to authenticated user
            $order = Order::where('id', $orderId)
                ->where('user_id', auth()->id())
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found.'
                ], 404);
            }

            //  Check if already paid
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been paid.'
                ], 400);
            }

            //  Prevent duplicate pending payments
            $existingPayment = Payment::where('order_id', $order->id)
                ->where('status', 'pending')
                ->first();

            if ($existingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'A payment request already exists.',
                    'payment' => $existingPayment
                ], 400);
            }

            //  Generate UUID
            $transactionUuid = (string) Str::uuid();

            // Step 5: Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_uuid' => $transactionUuid,
                'amount' => $order->total_amount,
                'method' => 'esewa',
                'status' => 'pending'
            ]);

            // Generate Signature
            $signature = $this->generateSignature(
                $order->total_amount,
                $transactionUuid
            );

            //  Return payment information
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully.',

                'payment_url' => config('services.esewa.base_url') . '/api/epay/main/v2/form',

                'payment' => [

                    'amount' => $order->total_amount,

                    'tax_amount' => 0,

                    'total_amount' => $order->total_amount,

                    'transaction_uuid' => $transactionUuid,

                    'product_code' => config('services.esewa.merchant_code'),

                    'product_service_charge' => 0,

                    'product_delivery_charge' => 0,

                    'success_url' => config('services.esewa.success_url'),

                    'failure_url' => config('services.esewa.failure_url'),

                    'signed_field_names' => 'total_amount,transaction_uuid,product_code',

                    'signature' => $signature

                ]

            ]);

        } catch (\Exception $e) 
        {

            return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
                ], 500);

         }

            
    }

    /**
     * Generate eSewa Signature
     */
    private function generateSignature($amount, $uuid)
    {
        $message =
            "total_amount={$amount},transaction_uuid={$uuid},product_code=" .
            config('services.esewa.merchant_code');

        return base64_encode(
            hash_hmac(
                'sha256',
                $message,
                config('services.esewa.secret_key'),
                true
            )
        );
    }
    public function success(Request $request)
    {
        try
        {
          /*
        eSewa redirects with:
        ?data=BASE64_ENCODED_JSON
        */

        if (!$request->has('data')) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment response.'
            ], 400);

        }

        // Decode Base64 response
        $decoded = json_decode(
            base64_decode($request->data),
            true
        );

        if (!$decoded) {

            return response()->json([
                'success' => false,
                'message' => 'Unable to decode eSewa response.'
            ], 400);

        }

        /*
        Example decoded response:

        {
            "transaction_code":"000ABC",
            "status":"COMPLETE",
            "total_amount":"13500",
            "transaction_uuid":"xxxx",
            "product_code":"EPAYTEST",
            "signed_field_names":"...",
            "signature":"..."
        }
        */

        $payment = Payment::where(
            'transaction_uuid',
            $decoded['transaction_uuid']
        )->first();

        if (!$payment) {

            return response()->json([
                'success' => false,
                'message' => 'Payment not found.'
            ],404);

        }

        // Save callback immediately for auditing
        $payment->update([
            'callback_response' => $decoded
        ]);

        // Verify with eSewa
        $verify = Http::get(
            config('services.esewa.base_url') .
            '/api/epay/transaction/status/',
            [
                'product_code' => config('services.esewa.merchant_code'),
                'total_amount' => $payment->amount,
                'transaction_uuid' => $payment->transaction_uuid
            ]
        );

        if (!$verify->successful()) {

            Log::error('eSewa verification failed', [
                'response' => $verify->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to verify payment.'
            ],500);

        }

        $verification = $verify->json();

        /*
        Example:

        {
          "status":"COMPLETE",
          "transaction_code":"000ABC",
          "ref_id":"123456"
        }
        */

        if (
            !isset($verification['status']) ||
            $verification['status'] !== 'COMPLETE'
        ) {

            $payment->update([
                'status' => 'failed'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed.'
            ],400);

        }

        DB::transaction(function () use ($payment, $verification) {

            $payment->update([

                'status' => 'paid',

                'transaction_code' =>
                    $verification['transaction_code'] ?? null

            ]);

            $payment->order->update([

                'status' => 'processing',

                'payment_status' => 'paid',

                'payment_method' => 'esewa'

            ]);

        });

        Log::info('Payment Successful', [

            'payment_id' => $payment->id,

            'transaction_uuid' =>
                $payment->transaction_uuid

        ]);

        return response()->json([

            'success' => true,

            'message' => 'Payment Verified Successfully.'

        ]);

    } catch (\Exception $e) {

        Log::error('Payment Success Error', [

            'message' => $e->getMessage()

        ]);

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ],500);
        }
    }
    public function failure(Request $request)
{
    Log::warning('Payment Failed', [

        'response' => $request->all()

    ]);

    return response()->json([

        'success' => false,

        'message' => 'Payment Failed.'

    ]);

}

}
