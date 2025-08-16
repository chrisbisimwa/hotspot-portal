<?php

declare(strict_types=1);

namespace App\Http\Responses;

trait ApiResponse
{
    /**
     * Return a successful API response
     */
    protected function success($data = null, array $meta = [], int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $meta,
            'errors' => null,
        ], $status);
    }

    /**
     * Return an error API response
     */
    protected function error(string $message, int $status = 400, $errors = null, array $meta = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'meta' => $meta,
            'errors' => $errors,
            'message' => $message,
        ], $status);
    }
}