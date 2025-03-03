<?php

namespace App\Models\Traits;

use App\Exceptions\Api\PaymentException;

trait HasPaymentStatus
{
    /**
     * Get all possible payment statuses.
     *
     * @return array<string>
     */
    public function getAllPaymentStatuses(): array
    {
        return [
            'pending',
            'processing',
            'paid',
            'failed',
            'refunded',
            'cancelled'
        ];
    }

    /**
     * Get allowed payment status transitions.
     *
     * @return array<string, array<string>>
     */
    public function getAllowedPaymentStatusTransitions(): array
    {
        return [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['paid', 'failed'],
            'paid' => ['refunded'],
            'failed' => ['pending', 'cancelled'],
            'refunded' => [],
            'cancelled' => ['pending']
        ];
    }

    /**
     * Check if a payment status transition is allowed.
     */
    public function canTransitionPaymentStatusTo(string $newStatus): bool
    {
        if ($this->payment_status === $newStatus) {
            return true;
        }

        $allowedTransitions = $this->getAllowedPaymentStatusTransitions();
        return isset($allowedTransitions[$this->payment_status]) &&
               in_array($newStatus, $allowedTransitions[$this->payment_status]);
    }

    /**
     * Transition to a new payment status.
     *
     * @throws PaymentException
     */
    public function transitionPaymentStatusTo(string $newStatus): bool
    {
        if (!in_array($newStatus, $this->getAllPaymentStatuses())) {
            throw PaymentException::badRequest(
                "Invalid payment status: {$newStatus}",
                ['allowed_statuses' => $this->getAllPaymentStatuses()]
            );
        }

        if (!$this->canTransitionPaymentStatusTo($newStatus)) {
            throw PaymentException::badRequest(
                "Cannot transition payment from {$this->payment_status} to {$newStatus}",
                [
                    'current_status' => $this->payment_status,
                    'allowed_transitions' => $this->getAllowedPaymentStatusTransitions()[$this->payment_status] ?? []
                ]
            );
        }

        $this->update(['payment_status' => $newStatus]);
        return true;
    }

    /**
     * Check if the model has a specific payment status.
     */
    public function hasPaymentStatus(string ...$statuses): bool
    {
        return in_array($this->payment_status, $statuses);
    }

    /**
     * Get human-readable payment status label.
     */
    public function getPaymentStatusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->payment_status));
    }

    /**
     * Get available payment status transitions.
     *
     * @return array<string>
     */
    public function getAvailablePaymentTransitions(): array
    {
        return $this->getAllowedPaymentStatusTransitions()[$this->payment_status] ?? [];
    }

    /**
     * Check if payment is in a final state.
     */
    public function isPaymentStatusFinal(): bool
    {
        return in_array($this->payment_status, ['paid', 'refunded', 'cancelled']);
    }

    /**
     * Check if payment can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->payment_status === 'paid';
    }
}