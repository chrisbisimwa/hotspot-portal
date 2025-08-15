<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Billing\Services\PaymentService;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReconcilePaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    private int $batchSize;

    public function __construct(?int $batchSize = null)
    {
        $this->batchSize = $batchSize ?? config('billing.reconcile_batch_size', 50);
    }

    public function handle(PaymentService $paymentService): void
    {
        $startTime = microtime(true);
        
        Log::info('ReconcilePaymentsJob: Starting payment reconciliation', [
            'batch_size' => $this->batchSize
        ]);

        try {
            // Get pending payments to reconcile
            $pendingPayments = Payment::whereIn('status', [
                PaymentStatus::PENDING->value,
                PaymentStatus::INITIATED->value,
                PaymentStatus::PROCESSING->value
            ])
            ->limit($this->batchSize)
            ->get();

            Log::info('ReconcilePaymentsJob: Found pending payments', [
                'count' => $pendingPayments->count()
            ]);

            $paymentsProcessed = 0;
            $transitionsLogged = 0;

            foreach ($pendingPayments as $payment) {
                $oldStatus = $payment->status;
                
                try {
                    // Verify payment status with gateway
                    $result = $paymentService->verify($payment);
                    
                    Log::info('ReconcilePaymentsJob: Payment status verified', [
                        'payment_id' => $payment->id,
                        'transaction_ref' => $payment->transaction_ref,
                        'old_status' => $oldStatus,
                        'new_status' => $payment->fresh()->status,
                        'verification_result' => $result
                    ]);
                    
                    $transitionsLogged++;
                    
                } catch (\Exception $e) {
                    Log::warning('ReconcilePaymentsJob: Payment verification failed', [
                        'payment_id' => $payment->id,
                        'transaction_ref' => $payment->transaction_ref,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $paymentsProcessed++;
            }

            $executionTime = microtime(true) - $startTime;
            Log::info('ReconcilePaymentsJob: Reconciliation completed', [
                'execution_time_seconds' => round($executionTime, 3),
                'payments_processed' => $paymentsProcessed,
                'transitions_logged' => $transitionsLogged
            ]);

        } catch (\Exception $e) {
            $executionTime = microtime(true) - $startTime;
            Log::error('ReconcilePaymentsJob: Reconciliation failed', [
                'error' => $e->getMessage(),
                'execution_time_seconds' => round($executionTime, 3)
            ]);
            
            throw $e;
        }
    }

    public function tags(): array
    {
        return ['billing', 'payments', 'reconciliation'];
    }
}