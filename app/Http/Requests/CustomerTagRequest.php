<?php

namespace App\Http\Requests;

use App\Rules\ValidPastDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'string',
                'max:64',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'description' => [
                'string',
                'max:254',
                'min:2',
                'required',
                'regex:/^[A-Za-z0-9 ]+$/',
            ],
            'color' => [
                'string',
                'max:9',
                'min:4',
                'required',
                'regex:/^[A-Za-z0-9#]+$/',
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
