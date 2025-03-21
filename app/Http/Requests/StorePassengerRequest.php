<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassengerRequest extends FormRequest
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
      // Passenger-related validation rules
      'survivorNumber' => 'sometimes|string|nullable',
      'firstName' => 'required|string',
      'middleName' => 'nullable|string',
      'lastName' => 'required|string',
      'dateOfBirth' => 'required|date',
      'citizenship' => 'required|string',
      'addressLine1' => 'required|string',
      'addressLine2' => 'nullable|string',
      'city' => 'required|string',
      'gender' => 'required|string',
      'state' => 'nullable|string',
      'zipCode' => 'required|string',
      'country' => 'required|string',
      'email' => 'required|email',
      'confirmEmail' => 'required|same:email',
      'newsletter' => 'nullable|boolean',
      'specialOptions' => 'nullable|array',
      'specialRequest' => 'nullable|string',
      'passengerOrder' => 'nullable|integer',
      'language' => 'nullable|string',
      'token' => 'sometimes|string',
      'language' => 'required|string',

      // Phone numbers
      'phoneNumber' => 'required|string',
      'emergencyPhoneNumber' => 'required|string',
      'emergencyContactName' => 'required|string',
    ];
  }
}
