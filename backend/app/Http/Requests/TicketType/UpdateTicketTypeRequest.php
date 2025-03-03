<?php

namespace App\Http\Requests\TicketType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $eventId = $this->route('eventId');
        $event = \App\Models\Event::findOrFail($eventId);
        return $this->user()->id === $event->organizer_id || 
               $this->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'quantity_available' => ['nullable', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'max_per_order' => ['nullable', 'integer', 'min:1'],
            'sales_start_date' => ['nullable', 'date'],
            'sales_end_date' => ['nullable', 'date', 'after:sales_start_date'],
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
            'name.max' => 'Ticket type name cannot exceed 255 characters',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price cannot be negative',
            'quantity_available.min' => 'Available quantity must be at least 1',
            'max_per_order.min' => 'Maximum tickets per order must be at least 1',
            'sales_end_date.after' => 'Sales end date must be after sales start date',
        ];
    }
}
