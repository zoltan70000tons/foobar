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
  // public function rules(): array
  // {
  //   return [
  //     // Passenger-related validation rules
  //     'language' => 'required|string',
  //     'addressLine1' => 'required|string',
  //     'addressLine2' => 'nullable|string',
  //     'city' => 'required|string',
  //     'state' => 'nullable|string',
  //     'zipCode' => 'required|string',
  //     'country' => 'required|string',
  //     'email' => 'required|email',
  //     'confirmEmail' => 'required|same:email',
  //     'info' => 'required|string', // aka 'hearAbout'
  //     'referralDetails' => 'sometimes|nullable|string',
  //     'travelInfo' => 'nullable|boolean',
  //     'newsletter' => 'nullable|boolean',
  //     'specialOptions' => 'nullable|array',
  //     'specialRequest' => 'nullable|string',
  //     'terms' => 'required|accepted',
  //     'paymentMethod' => 'required|string',
  //     'phoneNumber' => 'required|string',
  //     'emergencyPhoneNumber' => 'required|string',
  //     'emergencyContactName' => 'required|string',

  //     // Cart data and booking
  //     'bedConfig' => 'required|string',
  //     'cart.event_id' => 'required|string',
  //     'cart.cabin_type' => 'nullable|string',
  //     'cart.payment_plan' => 'required|string',
  //     'cart.number_of_installments' => 'nullable|string',
  //     'cart.reservation_id' => 'nullable|numeric',
  //     'cart.cabin_capacity' => 'required|numeric',
  //     'cart.cabin_category' => 'required|numeric',
  //     'cart.cabin_code' => 'nullable|string',
  //     'cart.cabin_price' => 'nullable|string',
  //     'cart.choose_your_cabin' => 'required|boolean',
  //     'cart.cabin_number' => 'nullable|numeric',
  //     'cart.price_total' => 'required|numeric',
  //     'cart.cabin_conf_accp' => 'required|boolean',
  //     'cart.single_t_agreement' => 'required|boolean',

  //     // Addons array
  //     'cart.addons' => 'nullable|array',
  //     'cart.addons.*' => 'nullable|array',
  //   ];
  // }
  public function rules(): array
  {
    return [
      // Passenger-related validation rules
      'language' => 'required|string',
      'address_line_1' => 'required|string',
      'address_line_2' => 'nullable|string',
      'city' => 'required|string',
      'state' => 'nullable|string',
      'zip_code' => 'required|string',
      'country' => 'required|string',
      'email' => 'required|email',
      'confirm_email' => 'required|same:email',
      'info' => 'required|string', // aka 'hearAbout'
      'referral_details' => 'sometimes|nullable|string',
      'travel_info' => 'nullable|boolean',
      'newsletter' => 'nullable|boolean',
      'special_options' => 'nullable|array',
      'special_request' => 'nullable|string',
      'terms' => 'required|accepted',
      'payment_method' => 'required|string',
      'phone_number' => 'required|string',
      'emergency_phone_number' => 'required|string',
      'emergency_contact_name' => 'required|string',

      // Cart data and booking
      'bed_config' => 'required|string',
      'cart.event_id' => 'required|string',
      'cart.cabin_type' => 'nullable|string',
      'cart.payment_plan' => 'required|string',
      'cart.number_of_installments' => 'nullable|string',
      'cart.reservation_id' => 'nullable|numeric|min:1',
      'cart.cabin_capacity' => 'required|numeric|min:0',
      'cart.cabin_category' => 'required|numeric|min:1',
      'cart.cabin_code' => 'nullable|string',
      'cart.cabin_price' => 'nullable|string',
      'cart.choose_your_cabin' => 'required|boolean',
      'cart.cabin_number' => 'nullable|numeric|min:1',
      'cart.price_total' => 'required|numeric|min:0',
      'cart.cabin_conf_accp' => 'required|boolean',
      'cart.single_t_agreement' => 'required|boolean',
      'cart.cabin_title' => 'required|string',

      // Addons array
      'cart.addons' => 'nullable|array',
      'cart.addons.*' => 'nullable|array',
    ];
  }
}
