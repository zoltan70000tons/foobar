<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return false;
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
      "firstName" => "required|string",
      "middleName" => "nullable|string",
      "lastName" => "required|string",
      "dateOfBirth" => "required|date",
      "citizenship" => "required|string",
      "addressLine1" => "required|string",
      "addressLine2" => "nullable|string",
      "city" => "required|string",
      "state" => "nullable|string",
      "zipCode" => "required|string",
      "country" => "required|string",
      "email" => "required|email",
      "confirmEmail" => "required|same:email",
      "info" => "required|string",
      "newsletter" => "nullable|boolean",
      "terms" => "required|accepted",

      // Phone numbers
      "phone.prefix" => "required|string",
      "phone.number" => "required|string",
      "emergencyContactPhone.prefix" => "required|string",
      "emergencyContactPhone.number" => "required|string",
      "emergencyContactName" => "required|string",

      // Cart data
      "cart.event" => "required|string",
      "cart.ticketType" => "nullable|string",
      "cart.paymentPlan" => "required|string",
      "cart.reservationId" => "nullable|number",
      "cart.cabinCapacity" => "required|number",
      "cart.cabinCategory" => "required|number",
      "cart.cabinCode" => "nullable|string",
      "cart.cabinPrice" => "required|string",
      "cart.cabinSelection" => "required|string",
      "cart.room" => "nullable|string",
      "cart.total" => "required|number",

      // Addons array
      "cart.addons" => "nullable|array",
      "cart.addons.*" => "string",
    ];
  }
}
