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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:draft,published,cancelled,completed'],
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
            'location.max' => 'Location cannot exceed 255 characters',
            'start_time.required' => 'Event start time is required',
            'start_time.after' => 'Event start time must be in the future',
            'end_time.required' => 'Event end time is required',
            'end_time.after' => 'Event end time must be after start time',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be a jpeg, png, jpg or gif file',
            'image.max' => 'Image size cannot exceed 2MB',
            'capacity.min' => 'Capacity must be at least 1',
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
