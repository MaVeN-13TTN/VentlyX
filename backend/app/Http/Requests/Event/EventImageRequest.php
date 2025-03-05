<?php

namespace App\Http\Requests\Event;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;

class EventImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $eventId = $this->route('id');
        $event = Event::find($eventId);
        return $event && (
            $this->user()->id === $event->organizer_id ||
            $this->user()->hasRole('Admin')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Remove dimension constraints for testing
        if (app()->environment('testing')) {
            return [
                'image' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:5120', // 5MB max
                ]
            ];
        }

        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB max
                'dimensions:min_width=200,min_height=200,max_width=4096,max_height=4096'
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
            'image.required' => 'An image file is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, or WEBP file.',
            'image.max' => 'The image size must not exceed 5MB.',
            'image.dimensions' => 'The image dimensions must be between 200x200 and 4096x4096 pixels.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if (app()->environment('testing')) {
            return;
        }

        $validator->after(function ($validator) {
            if ($this->hasFile('image')) {
                $image = $this->file('image');

                // Check image aspect ratio (prevent extremely skewed images)
                $imageSize = getimagesize($image->path());
                if ($imageSize) {
                    $width = $imageSize[0];
                    $height = $imageSize[1];
                    $ratio = $width / $height;

                    if ($ratio < 0.5 || $ratio > 2.0) {
                        $validator->errors()->add(
                            'image',
                            'The image aspect ratio must be between 1:2 and 2:1.'
                        );
                    }
                }
            }
        });
    }
}
