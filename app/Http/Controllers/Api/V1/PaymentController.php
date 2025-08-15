<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Billing\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Payment\InitiatePaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get a specific payment
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        // Check ownership
        if ($payment->user_id !== $request->user()->id) {
            return $this->error('Payment not found', 404, null, [
                'code' => 'PAYMENT_NOT_FOUND'
            ]);
        }
        
        return $this->success(new PaymentResource($payment->load('order')));
    }

    /**
     * Initiate payment for an order
     */
    public function initiate(InitiatePaymentRequest $request, Order $order): JsonResponse
    {
        // Check ownership
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404, null, [
                'code' => 'ORDER_NOT_FOUND'
            ]);
        }
        
        // Check if order is in a payable state
        if ($order->status !== 'pending') {
            return $this->error('Order cannot be paid', 400, null, [
                'code' => 'ORDER_NOT_PAYABLE'
            ]);
        }
        
        // Check if payment already exists for this order
        $existingPayment = Payment::where('order_id', $order->id)
            ->whereIn('status', ['success', 'processing', 'initiated'])
            ->first();
            
        if ($existingPayment) {
            return $this->error('Payment already exists for this order', 400, null, [
                'code' => 'PAYMENT_ALREADY_EXISTS'
            ]);
        }
        
        try {
            $validated = $request->validated();
            $provider = $validated['provider'] ?? 'serdipay';
            
            $payment = $this->paymentService->initiate($order, $provider);
            
            return $this->success(
                new PaymentResource($payment->load('order')),
                ['message' => 'Payment initiated successfully'],
                201
            );
        } catch (\Exception $e) {
            return $this->error('Failed to initiate payment: ' . $e->getMessage(), 500, null, [
                'code' => 'PAYMENT_INITIATION_FAILED'
            ]);
        }
    }
}