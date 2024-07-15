<?php

namespace App\Http\Requests\Role;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AddPermissionToRoleRequest extends FormRequest
{
   /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

      /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_id' => 'required|int',
            'permission_id' => 'nullable|int',
            'permissions' => 'nullable|array',
        ];
    }

    protected function withValidator(Validator $validator)
{
    $validator->after(function ($validator) {
        $data = $this->input('permissions');
        $permission_id = $this->input('permission_id');

        if (is_null($data) && is_null($permission_id)) {
            $validator->errors()->add('permissions', 'The permissions field is required when permission_id is not present.');
            $validator->errors()->add('permission_id', 'The permission_id field is required when data is not present.');
        }
    });
}

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(
            [
                'success' => false,
                'message' => 'Validation errors',
                'data' => $validator->errors(),
            ]
        ));
    }
}
