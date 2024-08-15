<?php

namespace App\Http\Requests\Team;

use App\Traits\JsonResponseTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UpdateMemberRequest extends FormRequest
{
    use JsonResponseTrait;
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
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'phone_number' => ['sometimes', 'phone:AUTO'],
            //'gender' => 'sometimes|string',
            //'middlename' => 'sometimes|string',
            'email' => 'required|email',
        ];
    }
}