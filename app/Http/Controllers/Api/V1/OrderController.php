<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    /**
     * Get user's orders with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', config('api.default_page_size')), config('api.max_page_size'));
        
        $orders = Order::with(['userProfile'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
            
        return $this->success(
            OrderResource::collection($orders),
            [
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ]
            ]
        );
    }

    /**
     * Create a new order
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        // Get the user profile
        $profile = UserProfile::where('id', $validated['user_profile_id'])
            ->where('is_active', true)
            ->firstOrFail();
            
        // Calculate pricing
        $unitPrice = $profile->price;
        $quantity = $validated['quantity'];
        $totalAmount = $unitPrice * $quantity;
        
        // Create order
        $order = Order::create([
            'user_id' => $request->user()->id,
            'user_profile_id' => $profile->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'requested_at' => now(),
        ]);
        
        return $this->success(
            new OrderResource($order->load('userProfile')),
            ['message' => 'Order created successfully'],
            201
        );
    }

    /**
     * Get a specific order
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        // Check ownership
        if ($order->user_id !== $request->user()->id) {
            return $this->error('Order not found', 404, null, [
                'code' => 'ORDER_NOT_FOUND'
            ]);
        }
        
        return $this->success(new OrderResource($order->load('userProfile')));
    }
}