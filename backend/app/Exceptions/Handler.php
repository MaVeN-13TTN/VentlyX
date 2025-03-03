<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Api\ApiException;
use App\Exceptions\Api\PaymentException;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
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
        // Handle API base exception - this should come first as more specific exceptions extend this
        $this->renderable(function (ApiException $e, $request) {
            if ($request->is('api/*')) {
                return $e->render();
            }
        });

        // Handle payment exceptions
        $this->renderable(function (PaymentException $e, $request) {
            if ($request->is('api/*')) {
                return $e->render();
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error' => 'Please login to access this resource'
                ], 401);
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $e->validator->errors()
                ], 422);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                    'error' => 'The requested resource could not be found'
                ], 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Not found',
                    'error' => 'The requested URL was not found'
                ], 404);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Method not allowed',
                    'error' => 'The requested method is not supported for this route'
                ], 405);
            }
        });

        $this->renderable(function (CardException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Payment failed',
                    'error' => $e->getMessage(),
                    'code' => $e->getStripeCode(),
                    'decline_code' => $e->getDeclineCode(),
                ], 400);
            }
        });

        $this->renderable(function (InvalidRequestException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Invalid payment request',
                    'error' => $e->getMessage()
                ], 400);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                // Determine status code properly, handling HttpExceptionInterface
                $status = 500;
                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                }

                if ($status === 500) {
                    Log::error('Unhandled exception: ' . $e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                return response()->json([
                    'message' => $status === 500 ? 'Server error' : 'Error',
                    'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred'
                ], $status);
            }
        });
    }
}
