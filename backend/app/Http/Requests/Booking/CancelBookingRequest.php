<?php

namespace App\Http\Requests\Booking;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booking = Booking::find($this->route('id'));
        return $booking && $booking->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // No additional rules needed as we're just validating the booking ID from the route
            // and checking the booking status in the controller
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
        $validator->after(function ($validator) {
            $booking = Booking::find($this->route('id'));
            if ($booking && $booking->status !== 'pending') {
                $validator->errors()->add('booking', 'Only pending bookings can be cancelled.');
            }
        });
    }
}
