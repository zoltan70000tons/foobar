<?php

namespace App\Http\Requests\Organization;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Traits\JsonResponseTrait;

class CreateOrganizationRequest extends FormRequest {
    use JsonResponseTrait;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'name' => 'required|string|max:30|unique:organizations,name',
            'user_name' => 'required|string|max:30',
            'email' => 'required|string',
            'password' => 'required|string',
            'terms' => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator) {
        return $this->errorResponse('Failed to create organization', 500, $validator->errors());
    }
}
