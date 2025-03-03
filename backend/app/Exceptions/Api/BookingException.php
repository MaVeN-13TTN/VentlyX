<?php

namespace App\Exceptions\Api;

class BookingException extends ApiException
{
    public static function soldOut(string $ticketType): self
    {
        return new static(
            "Tickets sold out for '{$ticketType}'",
            400,
            'ERR_TICKETS_SOLD_OUT'
        );
    }

    public static function insufficientTickets(string $ticketType, int $available, int $requested): self
    {
        return new static(
            "Not enough tickets available for '{$ticketType}'. Requested: {$requested}, Available: {$available}",
            400,
            'ERR_INSUFFICIENT_TICKETS',
            [
                'ticket_type' => $ticketType,
                'available' => $available,
                'requested' => $requested
            ]
        );
    }

    public static function exceedsMaxPerOrder(string $ticketType, int $maxAllowed): self
    {
        return new static(
            "Maximum {$maxAllowed} tickets allowed per order for '{$ticketType}'",
            400,
            'ERR_MAX_TICKETS_EXCEEDED',
            ['max_allowed' => $maxAllowed]
        );
    }

    public static function eventEnded(): self
    {
        return new static(
            'Cannot book tickets for an event that has already ended',
            400,
            'ERR_EVENT_ENDED'
        );
    }

    public static function bookingClosed(): self
    {
        return new static(
            'Ticket sales are closed for this event',
            400,
            'ERR_BOOKING_CLOSED'
        );
    }

    public static function alreadyCheckedIn(): self
    {
        return new static(
            'This ticket has already been checked in',
            400,
            'ERR_ALREADY_CHECKED_IN'
        );
    }

    public static function invalidStatus(string $currentStatus, array $allowedStatuses): self
    {
        return new static(
            'Invalid booking status for this operation',
            400,
            'ERR_INVALID_STATUS',
            [
                'current_status' => $currentStatus,
                'allowed_statuses' => $allowedStatuses
            ]
        );
    }

    public static function cancellationNotAllowed(string $reason): self
    {
        return new static(
            "Booking cannot be cancelled: {$reason}",
            400,
            'ERR_CANCELLATION_NOT_ALLOWED'
        );
    }
}