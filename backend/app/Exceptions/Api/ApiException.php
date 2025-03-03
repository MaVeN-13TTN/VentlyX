<?php

namespace App\Exceptions\Api;

use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected $statusCode = 500;
    protected $errorCode;
    protected $details;

    public function __construct(string $message = '', int $statusCode = 500, string $errorCode = '', array $details = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode ?: 'ERR_' . $statusCode;
        $this->details = $details;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'code' => $this->errorCode,
                'details' => $this->details,
            ]
        ], $this->statusCode);
    }

    public static function paymentRequired(string $message = 'Payment Required'): self
    {
        return new static($message, 402, 'ERR_PAYMENT_REQUIRED');
    }

    public static function resourceNotFound(string $resource, string $id = ''): self
    {
        $message = "{$resource} not found";
        if ($id) {
            $message .= " with ID: {$id}";
        }
        return new static($message, 404, 'ERR_NOT_FOUND');
    }

    public static function validationFailed(array $errors): self
    {
        return new static(
            'Validation failed',
            422,
            'ERR_VALIDATION_FAILED',
            ['validation_errors' => $errors]
        );
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return new static($message, 401, 'ERR_UNAUTHORIZED');
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return new static($message, 403, 'ERR_FORBIDDEN');
    }

    public static function tooManyRequests(string $message = 'Too Many Requests'): self
    {
        return new static($message, 429, 'ERR_RATE_LIMIT');
    }

    public static function serverError(string $message = 'Internal Server Error'): self
    {
        return new static($message, 500, 'ERR_SERVER');
    }

    public static function serviceUnavailable(string $message = 'Service Temporarily Unavailable'): self
    {
        return new static($message, 503, 'ERR_SERVICE_UNAVAILABLE');
    }

    public static function badRequest(string $message, array $details = []): self
    {
        return new static($message, 400, 'ERR_BAD_REQUEST', $details);
    }
}