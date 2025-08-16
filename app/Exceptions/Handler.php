<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle API requests with JSON responses
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with consistent JSON format
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        $status = 500;
        $message = 'Internal server error';
        $errors = null;
        $code = 'INTERNAL_ERROR';

        if ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation failed';
            $errors = $e->errors();
            $code = 'VALIDATION_ERROR';
        } elseif ($e instanceof ModelNotFoundException) {
            $status = 404;
            $message = 'Resource not found';
            $code = 'MODEL_NOT_FOUND';
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Authentication required';
            $code = 'AUTHENTICATION_REQUIRED';
        } elseif ($e instanceof AccessDeniedHttpException) {
            $status = 403;
            $message = 'Access denied';
            $code = 'ACCESS_DENIED';
        } elseif (method_exists($e, 'getStatusCode')) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: 'HTTP error';
        } else {
            $message = config('app.debug') ? $e->getMessage() : 'Internal server error';
        }

        return response()->json([
            'success' => false,
            'data' => null,
            'meta' => [
                'code' => $code,
            ],
            'errors' => $errors,
            'message' => $message,
        ], $status);
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        // For API requests, use our custom format
        if ($request->is('api/*')) {
            return response()->json([
                'success' => false,
                'data' => null,
                'meta' => [
                    'code' => 'VALIDATION_ERROR',
                ],
                'errors' => $exception->errors(),
                'message' => 'Validation failed',
            ], $exception->status);
        }

        return parent::invalidJson($request, $exception);
    }
}