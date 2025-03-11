<?php

namespace App\Http\Requests;

class CustomerCreateRequest extends CustomerRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
                'not_regex:/[<>{}]/',
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            '*.required' => 'The :attribute field is required. Please provide a valid :attribute.',
            '*.string' => 'The :attribute field is required. Please provide a valid :attribute.',
        ];
    }
}
