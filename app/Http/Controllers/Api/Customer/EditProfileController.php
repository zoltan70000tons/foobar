<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
}
