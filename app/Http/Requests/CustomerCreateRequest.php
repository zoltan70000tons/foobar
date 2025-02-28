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
            ],
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
            ],
            'survivor_number' => [
                'nullable',
                'max:9',
                'string',
            ]
        ]);
    }
}
