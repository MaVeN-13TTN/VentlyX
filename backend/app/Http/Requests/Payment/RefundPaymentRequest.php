<?php

namespace App\Http\Requests\Payment;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class RefundPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_id' => ['required', 'exists:payments,id'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
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
            $payment = Payment::find($this->input('payment_id'));
            if ($payment) {
                if ($payment->status !== 'completed') {
                    $validator->errors()->add(
                        'payment_id',
                        'Only completed payments can be refunded.'
                    );
                }
                if ($this->input('amount') && $this->input('amount') > $payment->amount) {
                    $validator->errors()->add(
                        'amount',
                        'Refund amount cannot exceed the original payment amount.'
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
            'payment_id.required' => 'A payment ID is required.',
            'payment_id.exists' => 'The specified payment does not exist.',
            'amount.numeric' => 'Refund amount must be a number.',
            'amount.min' => 'Refund amount must be greater than zero.',
            'reason.max' => 'Refund reason cannot exceed 255 characters.',
        ];
    }
}
