<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RescheduleBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $booking = $this->route('bookingCode');
        return $booking && $booking->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'schedule_id' => 'required|exists:schedules,id',
            'booking_date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'schedule_id.required' => 'Jadwal baru tidak boleh kosong',
            'schedule_id.exists' => 'Jadwal baru tidak valid',
            'booking_date.required' => 'Tanggal keberangkatan baru tidak boleh kosong',
            'booking_date.date_format' => 'Format tanggal tidak valid (YYYY-MM-DD)',
            'booking_date.after_or_equal' => 'Tanggal keberangkatan minimal hari ini',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422));
    }
}
