<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = Event::findOrFail($this->route('id'));
        return $this->user() && ($this->user()->hasRole('Admin') ||
            ($this->user()->hasRole('Organizer') && $event->organizer_id === $this->user()->id));
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start_time' => 'sometimes|date|after:now',
            'end_time' => 'sometimes|date|after:start_time',
            'location' => 'sometimes|string|max:255',
            'venue' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|in:Conference,Workshop,Seminar,Networking,Other',
            'max_capacity' => 'nullable|integer|min:1',
            'status' => 'sometimes|string|in:draft,published,cancelled',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.string' => 'The title must be a string.',
            'title.max' => 'The title must not exceed 255 characters.',
            'description.string' => 'The description must be a string.',
            'start_time.date' => 'The start time must be a valid date.',
            'start_time.after' => 'The start time must be in the future.',
            'end_time.date' => 'The end time must be a valid date.',
            'end_time.after' => 'The end time must be after the start time.',
            'location.string' => 'The location must be a string.',
            'location.max' => 'The location must not exceed 255 characters.',
            'venue.string' => 'The venue must be a string.',
            'venue.max' => 'The venue must not exceed 255 characters.',
            'category.string' => 'The category must be a string.',
            'category.in' => 'The selected category is invalid.',
            'max_capacity.integer' => 'The maximum capacity must be a number.',
            'max_capacity.min' => 'The maximum capacity must be at least 1.',
            'status.in' => 'The selected status is invalid.',
        ];
    }
}
