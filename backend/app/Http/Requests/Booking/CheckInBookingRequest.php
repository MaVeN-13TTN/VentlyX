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
        $booking = Booking::with(['event'])->findOrFail($this->route('id'));
        return $this->user()->hasRole('admin') ||
            ($booking->event && $this->user()->id === $booking->event->organizer_id);
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

            if ($booking->status !== 'confirmed') {
                $validator->errors()->add('booking', 'Invalid booking status: ' . $booking->status . '. Expected one of: [confirmed]');
                return;
            }

            if ($booking->checked_in_at) {
                $validator->errors()->add('booking', 'Booking has already been checked in');
                return;
            }

            if (!$booking->event) {
                return;
            }

            if (now()->lt($booking->event->start_time) || now()->gt($booking->event->end_time)) {
                $validator->errors()->add('booking', 'Check-in is only available during the event');
            }
        });
    }
}
