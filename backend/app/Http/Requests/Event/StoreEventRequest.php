<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->hasRole('Admin') || $this->user()->hasRole('Organizer'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'venue' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after_or_equal:start_time'],
            'category' => ['required', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:draft,published,cancelled,postponed,ended,archived'],
            'featured' => ['nullable', 'boolean'],
            'additional_info' => ['nullable', 'json'],
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
            'title.required' => 'Event title is required',
            'title.max' => 'Event title cannot exceed 255 characters',
            'description.required' => 'Event description is required',
            'location.required' => 'Event location is required',
            'venue.required' => 'Event venue is required',
            'location.max' => 'Location cannot exceed 255 characters',
            'start_time.required' => 'Event start time is required',
            'end_time.required' => 'Event end time is required',
            'end_time.after_or_equal' => 'Event end time must be after or equal to start time',
            'category.required' => 'Event category is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be a jpeg, png, jpg or gif file',
            'image.max' => 'Image size cannot exceed 2MB',
            'max_capacity.min' => 'Maximum capacity must be at least 1',
            'status.in' => 'Invalid event status',
            'additional_info.json' => 'Additional info must be a valid JSON string',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('featured') && is_string($this->featured)) {
            $this->merge([
                'featured' => $this->featured === 'true',
            ]);
        }
    }
}
