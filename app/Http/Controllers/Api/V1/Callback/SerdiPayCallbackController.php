<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Callback;

use App\Domain\Billing\Contracts\PaymentGatewayInterface;
use App\Domain\Billing\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SerdiPayCallbackController extends Controller
{
    use ApiResponse;

    private PaymentService $paymentService;
    private PaymentGatewayInterface $gateway;

    public function __construct(PaymentService $paymentService, PaymentGatewayInterface $gateway)
    {
        $this->paymentService = $paymentService;
        $this->gateway = $gateway;
    }

    /**
     * Handle SerdiPay payment callback
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $payload = $request->all();
            $headers = $request->headers->all();
            
            Log::info('SerdiPay callback received', [
                'payload' => $payload,
                'headers' => array_keys($headers)
            ]);
            
            // Verify signature and record callback
            $payment = $this->paymentService->recordCallback($payload, $headers);
            
            Log::info('SerdiPay callback processed successfully', [
                'payment_id' => $payment->id,
                'transaction_ref' => $payment->transaction_ref,
                'status' => $payment->status
            ]);
            
            return $this->success([
                'received' => true,
                'processed' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status
            ]);
            
        } catch (\Exception $e) {
            Log::error('SerdiPay callback processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);
            
            return $this->error('Callback processing failed: ' . $e->getMessage(), 500, null, [
                'code' => 'CALLBACK_PROCESSING_FAILED'
            ]);
        }
    }
}