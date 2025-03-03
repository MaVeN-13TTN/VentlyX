<?php

namespace App\Exceptions\Api;

class PaymentException extends ApiException
{
    public static function paymentFailed(string $reason, array $details = []): self
    {
        return new static(
            "Payment failed: {$reason}",
            400,
            'ERR_PAYMENT_FAILED',
            $details
        );
    }

    public static function invalidPaymentMethod(string $method): self
    {
        return new static(
            "Invalid payment method: {$method}",
            400,
            'ERR_INVALID_PAYMENT_METHOD'
        );
    }

    public static function alreadyPaid(): self
    {
        return new static(
            'This booking has already been paid for',
            400,
            'ERR_ALREADY_PAID'
        );
    }

    public static function refundNotAllowed(string $reason): self
    {
        return new static(
            "Refund not allowed: {$reason}",
            400,
            'ERR_REFUND_NOT_ALLOWED'
        );
    }

    public static function invalidAmount(float $requested, float $maximum): self
    {
        return new static(
            "Invalid refund amount. Maximum allowed: {$maximum}, Requested: {$requested}",
            400,
            'ERR_INVALID_REFUND_AMOUNT',
            [
                'maximum_amount' => $maximum,
                'requested_amount' => $requested
            ]
        );
    }

    public static function paymentProcessingError(string $message, array $details = []): self
    {
        return new static(
            "Payment processing error: {$message}",
            500,
            'ERR_PAYMENT_PROCESSING',
            $details
        );
    }

    public static function paymentProviderError(string $provider, string $error): self
    {
        return new static(
            "Payment provider error ({$provider}): {$error}",
            503,
            'ERR_PAYMENT_PROVIDER'
        );
    }
}