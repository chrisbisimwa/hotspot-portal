<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentsController extends Controller
{
    use ApiResponse;

    /**
     * Get all payments (admin only) with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        $payments = Payment::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return $this->success(
            PaymentResource::collection($payments),
            [
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'last_page' => $payments->lastPage(),
                ]
            ]
        );
    }
}