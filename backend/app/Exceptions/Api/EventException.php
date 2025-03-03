<?php

namespace App\Exceptions\Api;

class EventException extends ApiException
{
    public static function eventEnded(string $eventName): self
    {
        return new static(
            "Event '{$eventName}' has already ended",
            400,
            'ERR_EVENT_ENDED'
        );
    }

    public static function eventCancelled(): self
    {
        return new static(
            'This event has been cancelled',
            400,
            'ERR_EVENT_CANCELLED'
        );
    }

    public static function ticketSalesEnded(string $eventName): self
    {
        return new static(
            "Ticket sales have ended for '{$eventName}'",
            400,
            'ERR_TICKET_SALES_ENDED'
        );
    }

    public static function invalidDateRange(): self
    {
        return new static(
            'End date must be after start date',
            400,
            'ERR_INVALID_DATE_RANGE'
        );
    }

    public static function pastStartDate(): self
    {
        return new static(
            'Event start date cannot be in the past',
            400,
            'ERR_PAST_START_DATE'
        );
    }

    public static function maxCapacityExceeded(int $capacity, int $maxAllowed): self
    {
        return new static(
            "Maximum event capacity of {$maxAllowed} exceeded. Requested: {$capacity}",
            400,
            'ERR_MAX_CAPACITY_EXCEEDED',
            [
                'max_allowed' => $maxAllowed,
                'requested' => $capacity
            ]
        );
    }

    public static function organizerLimitExceeded(int $limit): self
    {
        return new static(
            "You have reached the maximum limit of {$limit} active events",
            400,
            'ERR_ORGANIZER_LIMIT_EXCEEDED',
            ['limit' => $limit]
        );
    }

    public static function notOrganizer(): self
    {
        return new static(
            'You are not the organizer of this event',
            403,
            'ERR_NOT_ORGANIZER'
        );
    }

    public static function invalidStatus(string $currentStatus, array $allowedStatuses): self
    {
        return new static(
            'Invalid event status for this operation',
            400,
            'ERR_INVALID_STATUS',
            [
                'current_status' => $currentStatus,
                'allowed_statuses' => $allowedStatuses
            ]
        );
    }

    public static function hasActiveBookings(): self
    {
        return new static(
            'Cannot modify event with active bookings',
            400,
            'ERR_HAS_ACTIVE_BOOKINGS'
        );
    }

    public static function imageUploadFailed(string $reason): self
    {
        return new static(
            "Failed to upload event image: {$reason}",
            400,
            'ERR_IMAGE_UPLOAD_FAILED'
        );
    }
}