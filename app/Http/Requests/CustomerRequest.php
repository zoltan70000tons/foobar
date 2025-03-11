<?php

namespace App\Http\Requests;

use App\Rules\ValidPastDate;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => [
                'string',
                'max:13',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'middle_name' => [
                'string',
                'max:13',
                'nullable',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'last_name' => [
                'string',
                'max:18',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'phone' => [
                'string',
                'required',
                'regex:/^\+?[0-9]{7,15}$/',
                'different:emergency_c_phone',
            ],
            'language' => [
                'string',
                'required',
            ],
            'gender' => [
                'string',
                'required',
                'min:1',
            ],
            'country' => [
                'string',
                'required',
            ],
            'state' => [
                'string',
                'max:20',
                'nullable',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'address_first' => [
                'string',
                'max:50',
                'required',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'address_second' => [
                'string',
                'max:50',
                'nullable',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'city' => [
                'string',
                'max:30',
                'required',
                'regex:/^[#.0-9a-zA-Z\s,-]+$/'
            ],
            'postal_code' => [
                'string',
                'max:10',
                'required',
                'regex:/^[A-Za-z0-9 -]+$/',
            ],
            'emergency_c_name' => [
                'string',
                'max:75',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'emergency_c_phone' => [
                'string',
                'required',
                'regex:/^\+?[0-9]{7,15}$/',
                'different:phone',
            ],
            'citizenship' => [
                'required',
                'string',
                'min:2',
            ],
            'year' => [
                'required',
                'string',
                'min:4',
                'max:4',
                'regex:/^[0-9]*$/',
                'gt:1909',
                'before_or_equal:' . date('Y'),
                new ValidPastDate(),
            ],
            'month' => [
                'required',
                'string',
                'min:2',
                'max:2',
                'regex:/^[0-9]*$/',
                'gt:0',
                'lt:13',
            ],
            'day' => [
                'required',
                'string',
                'min:2',
                'max:2',
                'regex:/^[0-9]*$/',
                'gt:0',
                'lt:32',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required. Please provide a valid :attribute.',
            '*.string' => 'The :attribute field is required. Please provide a valid :attribute.',
        ];
    }
}
