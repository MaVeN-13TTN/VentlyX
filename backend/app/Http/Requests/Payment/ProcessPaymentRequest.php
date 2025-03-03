<?php

namespace App\Http\Requests\Payment;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $booking = Booking::find($this->input('booking_id'));
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
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_method' => ['required', 'string', 'in:stripe,paypal'],
            'payment_token' => ['required', 'string'], // Token from Stripe or PayPal
            'currency' => ['required', 'string', 'size:3'], // ISO 4217 currency code
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
            $booking = Booking::find($this->input('booking_id'));
            if ($booking) {
                if ($booking->status !== 'pending') {
                    $validator->errors()->add(
                        'booking_id', 
                        'Payment can only be processed for pending bookings.'
                    );
                }
                if ($booking->payment_status !== 'pending') {
                    $validator->errors()->add(
                        'booking_id', 
                        'This booking has already been paid for or payment is being processed.'
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'booking_id.required' => 'A booking ID is required.',
            'booking_id.exists' => 'The specified booking does not exist.',
            'payment_method.required' => 'Please specify a payment method.',
            'payment_method.in' => 'Invalid payment method. Supported methods are Stripe and PayPal.',
            'payment_token.required' => 'Payment token is required.',
            'currency.required' => 'Currency code is required.',
            'currency.size' => 'Currency code must be a valid 3-letter ISO code.',
        ];
    }
}
