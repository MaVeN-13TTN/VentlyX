<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $eventId = $this->route('id');
        $event = Event::findOrFail($eventId);

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
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_time' => ['nullable', 'date', 'after:now'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
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
            'title.max' => 'Event title cannot exceed 255 characters',
            'location.max' => 'Location cannot exceed 255 characters',
            'start_time.after' => 'Event start time must be in the future',
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
