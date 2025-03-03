<?php

namespace App\Http\Requests\Booking;

use App\Models\TicketType;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'exists:events,id'],
            'ticket_type_id' => [
                'required',
                'exists:ticket_types,id',
                function ($attribute, $value, $fail) {
                    $ticketType = TicketType::find($value);
                    if ($ticketType && $ticketType->event_id != $this->input('event_id')) {
                        $fail('The selected ticket type does not belong to this event.');
                    }
                }
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    $ticketType = TicketType::find($this->input('ticket_type_id'));
                    if ($ticketType) {
                        if (!$ticketType->is_available) {
                            $fail('Tickets are not available for sale.');
                        }
                        if ($value > $ticketType->tickets_remaining) {
                            $fail('Not enough tickets available. Only ' . $ticketType->tickets_remaining . ' tickets remaining.');
                        }
                        if ($ticketType->max_per_order && $value > $ticketType->max_per_order) {
                            $fail('Maximum ' . $ticketType->max_per_order . ' tickets allowed per order.');
                        }
                    }
                }
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'An event must be selected.',
            'event_id.exists' => 'The selected event is invalid.',
            'ticket_type_id.required' => 'A ticket type must be selected.',
            'ticket_type_id.exists' => 'The selected ticket type is invalid.',
            'quantity.required' => 'Please specify the number of tickets.',
            'quantity.integer' => 'The quantity must be a whole number.',
            'quantity.min' => 'You must book at least one ticket.',
        ];
    }
}
