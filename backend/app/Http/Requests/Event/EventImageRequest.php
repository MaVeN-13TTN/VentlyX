<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class EventImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $event = Event::findOrFail($this->route('id'));
        return $this->user()->hasRole('Admin') || $event->organizer_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'An image file is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, or JPG file.',
            'image.max' => 'The image size must not exceed 2MB.'
        ];
    }
}
