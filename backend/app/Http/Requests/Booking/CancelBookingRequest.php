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
        return [];
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
            $booking = Booking::with('event')->find($this->route('id'));

            if (!$booking) {
                return;
            }

            if ($booking->status === 'cancelled') {
                $validator->errors()->add('booking', 'Booking is already cancelled');
                return;
            }

            if ($booking->checked_in_at) {
                $validator->errors()->add('booking', 'Cannot cancel a booking that has been checked in');
                return;
            }

            if ($booking->event && now()->gte($booking->event->start_time)) {
                $validator->errors()->add('booking', 'Cannot cancel booking after event has started');
            }
        });
    }
}
