<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CustomerDetail;
use Illuminate\Support\Facades\Auth;

class EditProfileController extends Controller
{
    /**
     * Get the list of all customers with their related data.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Fetch all customers with their related details, membership types, and addresses
        $customers = Customer::with(['details', 'membershipTypes', 'addresses'])->get();

        // Return a JSON response with the customer data
        return response()->json([
            'success' => true,
            'data' => $customers,
        ], 200);
    }

    /**
     * Get a specific customer by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function getAccountIntel(string $id): JsonResponse
    {
        // Fetch the customer by ID with related details, membership types, and addresses
        $customer = Customer::with(['details', 'membershipTypes', 'addresses'])->find($id);

        // Check if the customer exists
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        // Return the customer data
        return response()->json([
            'success' => true,
            'data' => $customer,
        ], 200);
    }

        /**
     * Get specific customer details by customer ID (first_name, middle_name, last_name, gender, dob, phone, citizenship).
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function getCustomerDetails(string $id): JsonResponse
    {
        // Fetch the customer details by customer_id
        $customerDetail = CustomerDetail::where('customer_id', $id)->select(
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'dob',
            'phone',
            'citizenship'
        )->first();

        // Check if the customer details exist
        if (!$customerDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Customer details not found',
            ], 404);
        }

        // Return the customer details
        return response()->json([
            'success' => true,
            'data' => $customerDetail,
        ], 200);
    }

    /**
     * Update the preferred language of a customer.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function updatePreferredLanguage(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|in:ENG,DEU,SPA',
        ]);

        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Find the customer's details
        $customerDetail = CustomerDetail::where('customer_id', $customer->id)->first();

        if (!$customerDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Customer details not found',
            ], 404);
        }

        // Update the language field
        $customerDetail->language = $request->language;
        $customerDetail->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferred language updated successfully',
        ], 200);
    }
}
