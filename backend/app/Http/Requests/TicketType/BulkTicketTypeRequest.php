<?php

namespace App\Http\Requests\TicketType;

use Illuminate\Foundation\Http\FormRequest;

class BulkTicketTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'ticket_types' => ['required', 'array', 'min:1'],
            'ticket_types.*.name' => ['required', 'string', 'max:255'],
            'ticket_types.*.price' => ['required', 'numeric', 'min:0'],
            'ticket_types.*.quantity' => ['required', 'integer', 'min:1'],
            'ticket_types.*.description' => ['nullable', 'string'],
            'ticket_types.*.max_per_order' => ['nullable', 'integer', 'min:1'],
            'ticket_types.*.sales_start_date' => ['nullable', 'date'],
            'ticket_types.*.sales_end_date' => ['nullable', 'date', 'after:sales_start_date'],
            'ticket_types.*.id' => ['sometimes', 'required', 'exists:ticket_types,id'], // For updates
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
            'ticket_types.required' => 'At least one ticket type is required',
            'ticket_types.*.name.required' => 'Ticket type name is required',
            'ticket_types.*.price.required' => 'Ticket type price is required',
            'ticket_types.*.price.min' => 'Ticket type price must be at least 0',
            'ticket_types.*.quantity.required' => 'Ticket type quantity is required',
            'ticket_types.*.quantity.min' => 'Ticket type quantity must be at least 1',
            'ticket_types.*.sales_end_date.after' => 'Sales end date must be after sales start date',
        ];
    }
}
