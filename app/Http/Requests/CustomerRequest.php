<?php

namespace App\Http\Requests;

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
            ],
            'middle_name' => [
                'string',
                'max:13',
                'nullable',
            ],
            'last_name' => [
                'string',
                'max:18',
                'min:2',
                'required',
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
            ],
            'address_first' => [
                'string',
                'max:50',
                'required',
            ],
            'address_second' => [
                'string',
                'max:50',
                'nullable',
            ],
            'city' => [
                'string',
                'max:30',
                'required',
            ],
            'postal_code' => [
                'string',
                'max:10',
                'required',
            ],
            'emergency_c_name' => [
                'string',
                'max:75',
                'required',
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
            ],
            'month' => [
                'required',
                'string',
                'min:2',
                'max:2',
            ],
            'day' => [
                'required',
                'string',
                'min:2',
                'max:2',
            ],
        ];
    }
}
