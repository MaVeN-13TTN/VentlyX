<?php

namespace App\Http\Requests\Booking;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class CheckInBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booking = Booking::with('event')->find($this->route('id'));
        return $booking && (
            $this->user()->hasRole('Admin') || 
            $this->user()->id === $booking->event->organizer_id
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // No additional rules needed as we're validating through withValidator
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
            if ($booking) {
                if ($booking->status !== 'confirmed') {
                    $validator->errors()->add('booking', 'Only confirmed bookings can be checked in.');
                }
                if ($booking->checked_in_at) {
                    $validator->errors()->add('booking', 'Booking has already been checked in.');
                }
            }
        });
    }
}
