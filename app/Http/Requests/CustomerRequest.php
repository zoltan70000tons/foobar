<?php

namespace App\Http\Requests;

use App\Rules\ValidPastDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array {
        return [
            'first_name' => ['string', 'max:13', 'min:2', 'required', 'regex:/^[A-Za-z0-9 ]+$/'],
            'middle_name' => ['string', 'max:13', 'nullable', 'regex:/^[A-Za-z0-9 ]+$/'],
            'last_name' => ['string', 'max:18', 'min:2', 'required', 'regex:/^[A-Za-z0-9 ]+$/'],
            'phone' => ['string', 'required', 'regex:/^\+?[0-9]{7,15}$/', 'different:emergency_c_phone'],
            'language' => ['string', 'required'],
            'gender' => ['string', 'required', 'min:1'],
            'country' => ['string', 'required'],
            'state' => ['string', 'max:254', 'nullable', 'regex:/^[#.\p{L}0-9\s,-]+$/u'],
            'address_first' => ['string', 'max:50', 'required', 'regex:/^[#.0-9a-zA-Z\s,-]+$/'],
            'address_second' => ['string', 'max:50', 'nullable', 'regex:/^[#.0-9a-zA-Z\s,-]+$/'],
            'city' => ['string', 'max:30', 'required', 'regex:/^[#.0-9a-zA-Z\s,-]+$/'],
            'postal_code' => ['string', 'max:10', 'required', 'regex:/^[A-Za-z0-9 -]+$/'],
            'emergency_c_name' => ['string', 'max:75', 'required', 'regex:/^[A-Za-z0-9 ]+$/'],
            'emergency_c_phone' => ['string', 'required', 'regex:/^\+?[0-9]{7,15}$/', 'different:phone'],
            'citizenship' => ['required', 'string', 'min:2'],
            'dob' => [
                'required',
                'string',
                //'date_format:D, d M Y H:i:s T',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
                'not_regex:/[<>{}]/',
            ],
        ];
    }

    public function messages(): array {
        return [
            '*.required' => 'The :attribute field is required. Please provide a valid :attribute.',
            '*.string' => 'The :attribute field is required. Please provide a valid :attribute.',
        ];
    }
}
