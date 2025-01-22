<?php

namespace App\Http\Requests\Team;

use App\Rules\NoForbiddenCharacters;
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
            'firstname' => ['required', 'string', new NoForbiddenCharacters()],
            'lastname' => ['required', 'string', new NoForbiddenCharacters()],
            'phone_number' => ['sometimes', 'phone:AUTO'], // Validate if present
            'gender' => ['required', 'string', new NoForbiddenCharacters()],
            'middlename' => ['sometimes', 'string', new NoForbiddenCharacters()], // Validate if present
            'email' => 'required|email',
        ];
    }
}
