<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
            'passengers' => 'required|array|min:1',
            'passengers.*.name' => 'required|string|max:255',
            'passengers.*.id_number' => 'required|string|max:30',
            'passengers.*.id_type' => 'required|in:KTP,SIM,PASPOR',
            'passengers.*.dob' => 'required|date_format:Y-m-d',
            'passengers.*.gender' => 'required|in:MALE,FEMALE',
            'vehicles' => 'nullable|array',
            'vehicles.*.type' => 'required_with:vehicles|in:MOTORCYCLE,CAR,BUS,TRUCK',
            'vehicles.*.license_plate' => 'required_with:vehicles|string|max:20',
            'vehicles.*.weight' => 'nullable|numeric',
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
            'schedule_id.required' => 'Jadwal tidak boleh kosong',
            'schedule_id.exists' => 'Jadwal tidak valid',
            'booking_date.required' => 'Tanggal keberangkatan tidak boleh kosong',
            'booking_date.date_format' => 'Format tanggal tidak valid (YYYY-MM-DD)',
            'booking_date.after_or_equal' => 'Tanggal keberangkatan minimal hari ini',
            'passengers.required' => 'Data penumpang tidak boleh kosong',
            'passengers.min' => 'Minimal 1 penumpang',
            'passengers.*.name.required' => 'Nama penumpang tidak boleh kosong',
            'passengers.*.id_number.required' => 'Nomor identitas tidak boleh kosong',
            'passengers.*.id_type.required' => 'Tipe identitas tidak boleh kosong',
            'passengers.*.id_type.in' => 'Tipe identitas tidak valid',
            'passengers.*.dob.required' => 'Tanggal lahir tidak boleh kosong',
            'passengers.*.dob.date_format' => 'Format tanggal lahir tidak valid (YYYY-MM-DD)',
            'passengers.*.gender.required' => 'Jenis kelamin tidak boleh kosong',
            'passengers.*.gender.in' => 'Jenis kelamin tidak valid',
            'vehicles.*.type.required_with' => 'Tipe kendaraan tidak boleh kosong',
            'vehicles.*.type.in' => 'Tipe kendaraan tidak valid',
            'vehicles.*.license_plate.required_with' => 'Plat nomor kendaraan tidak boleh kosong',
            'vehicles.*.weight.numeric' => 'Berat kendaraan harus berupa angka',
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
