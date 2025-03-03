<?php

namespace App\Exceptions\Api;

class TicketException extends ApiException
{
    public static function invalidPrice(float $price, float $minPrice): self
    {
        return new static(
            "Invalid ticket price. Minimum price allowed is {$minPrice}",
            400,
            'ERR_INVALID_PRICE',
            [
                'min_price' => $minPrice,
                'provided_price' => $price
            ]
        );
    }

    public static function quantityExceedsCapacity(int $requested, int $remaining): self
    {
        return new static(
            "Requested quantity exceeds event capacity. Available: {$remaining}",
            400,
            'ERR_EXCEEDS_CAPACITY',
            [
                'available' => $remaining,
                'requested' => $requested
            ]
        );
    }

    public static function invalidSalesDate(string $reason): self
    {
        return new static(
            "Invalid ticket sales date: {$reason}",
            400,
            'ERR_INVALID_SALES_DATE'
        );
    }

    public static function ticketTypeLimit(int $limit): self
    {
        return new static(
            "Maximum limit of {$limit} ticket types per event reached",
            400,
            'ERR_TICKET_TYPE_LIMIT',
            ['limit' => $limit]
        );
    }

    public static function hasActiveBookings(string $ticketType): self
    {
        return new static(
            "Cannot modify ticket type '{$ticketType}' with active bookings",
            400,
            'ERR_ACTIVE_BOOKINGS'
        );
    }

    public static function invalidPriceChange(string $ticketType): self
    {
        return new static(
            "Cannot change price for ticket type '{$ticketType}' with existing bookings",
            400,
            'ERR_INVALID_PRICE_CHANGE'
        );
    }

    public static function salesEnded(string $ticketType): self
    {
        return new static(
            "Sales have ended for ticket type '{$ticketType}'",
            400,
            'ERR_SALES_ENDED'
        );
    }

    public static function notAvailable(string $ticketType): self
    {
        return new static(
            "Ticket type '{$ticketType}' is not available for sale",
            400,
            'ERR_NOT_AVAILABLE'
        );
    }

    public static function invalidMaxPerOrder(int $max, int $total): self
    {
        return new static(
            "Maximum tickets per order ({$max}) cannot exceed total available tickets ({$total})",
            400,
            'ERR_INVALID_MAX_PER_ORDER',
            [
                'max_per_order' => $max,
                'total_available' => $total
            ]
        );
    }
}