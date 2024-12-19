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
      "specialRequest" => "nullable|string",
      "terms" => "required|accepted",
      "paymentMethod" => "required|string",

      // Phone numbers
      // "phone.prefix" => "required|string",
      "phoneNumber" => "required|string",
      // "emergencyContactPhone.prefix" => "required|string",
      "emergencyPhoneNumber" => "required|string",
      "emergencyContactName" => "required|string",

      // Cart data
      "cart.event_id" => "required|string",
      "cart.cabin_type" => "nullable|string",
      "cart.payment_plan" => "required|string",
      "cart.reservation_id" => "nullable|numeric",
      "cart.cabin_capacity" => "required|numeric",
      "cart.cabin_category" => "required|numeric",
      "cart.cabin_code" => "nullable|string",
      "cart.cabin_price" => "nullable|string",
      "cart.choose_your_cabin" => "required|boolean",
      "cart.cabin_number" => "nullable|numeric",
      "cart.price_total" => "required|numeric",
      "cart.cabin_conf_accp" => "required|boolean",
      "cart.single_t_agreement" => "required|boolean",

      // Addons array
      "cart.addons" => "nullable|array",
      "cart.addons.*" => "nullable|array",
    ];
  }
}
