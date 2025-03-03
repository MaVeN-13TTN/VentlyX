<?php

namespace App\Models\Traits;

use App\Exceptions\Api\ApiException;

trait HasStatuses
{
    /**
     * Get the allowed status transitions.
     *
     * @return array<string, array<string>>
     */
    abstract public function getAllowedStatusTransitions(): array;

    /**
     * Get all possible statuses.
     *
     * @return array<string>
     */
    abstract public function getAllStatuses(): array;

    /**
     * Check if a status transition is allowed.
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->status === $newStatus) {
            return true;
        }

        $allowedTransitions = $this->getAllowedStatusTransitions();
        return isset($allowedTransitions[$this->status]) &&
               in_array($newStatus, $allowedTransitions[$this->status]);
    }

    /**
     * Transition to a new status.
     *
     * @throws ApiException
     */
    public function transitionTo(string $newStatus): bool
    {
        if (!in_array($newStatus, $this->getAllStatuses())) {
            throw ApiException::badRequest(
                "Invalid status: {$newStatus}",
                ['allowed_statuses' => $this->getAllStatuses()]
            );
        }

        if (!$this->canTransitionTo($newStatus)) {
            throw ApiException::badRequest(
                "Cannot transition from {$this->status} to {$newStatus}",
                [
                    'current_status' => $this->status,
                    'allowed_transitions' => $this->getAllowedStatusTransitions()[$this->status] ?? []
                ]
            );
        }

        $this->update(['status' => $newStatus]);
        return true;
    }

    /**
     * Check if the model has a specific status.
     */
    public function hasStatus(string ...$statuses): bool
    {
        return in_array($this->status, $statuses);
    }

    /**
     * Get human-readable status label.
     */
    public function getStatusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Get available status transitions.
     *
     * @return array<string>
     */
    public function getAvailableTransitions(): array
    {
        return $this->getAllowedStatusTransitions()[$this->status] ?? [];
    }
}