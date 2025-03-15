<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->hasRole('Admin') || $this->user()->hasRole('Organizer'));
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'location' => 'required|string|max:255',
            'venue' => 'required|string|max:255',
            'category' => 'required|string|in:Conference,Workshop,Seminar,Networking,Other',
            'max_capacity' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'nullable|string|in:draft,published',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The event title is required.',
            'description.required' => 'The event description is required.',
            'start_time.required' => 'The start time is required.',
            'start_time.date' => 'The start time must be a valid date.',
            'start_time.after' => 'The start time must be in the future.',
            'end_time.required' => 'The end time is required.',
            'end_time.date' => 'The end time must be a valid date.',
            'end_time.after' => 'The end time must be after the start time.',
            'location.required' => 'The event location is required.',
            'venue.required' => 'The event venue is required.',
            'category.required' => 'The event category is required.',
            'category.in' => 'The selected category is invalid.',
            'max_capacity.integer' => 'The maximum capacity must be a number.',
            'max_capacity.min' => 'The maximum capacity must be at least 1.',
        ];
    }
}
