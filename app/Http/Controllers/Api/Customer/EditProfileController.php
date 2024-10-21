<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\CustomerDetail;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

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
        $customers = User::with(['details', 'membershipTypes'])->get();

        // Return a JSON response with the customer data
        return response()->json([
            'success' => true,
            'data' => $customers,
        ], 200);
    }

    /**
     * Get a specific customer by ID.
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function getAccountIntel(string $id): JsonResponse
    {

        $user = Auth::user();
        $customer = $user->hasRole('Customer') ? $user : null;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }
    
        $user = User::with(['detail', 'membershipTypes', 'survivorNumber'])->find($id);
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
    
        return response()->json([
            'success' => true,
            'data' => $user,
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

        $user = Auth::user();
        $customer = $user->hasRole('Customer') ? $user : null;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $userDetail = UserDetail::where('user_id', '=', $customer->id)->select(
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'dob',
            'phone',
            'citizenship',
            'language',
            'emergency_c_name',
            'emergency_c_phone'
        )->first();
    
        $customerAddress = CustomerAddress::where('user_id', '=', $customer->id)->select(
            'address_first',
            'address_second',
            'city',
            'state',
            'postal_code',
            'country'
        )->first();
    
        if (!$userDetail || !$customerAddress) {
            return response()->json([
                'success' => false,
                'message' => 'User details or address not found',
            ], 404);
        }
    
        $response = [
            'user_details' => $userDetail,
            'customer_address' => $customerAddress,
        ];
    
        return response()->json([
            'success' => true,
            'data' => $response,
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

        $customerDetail = CustomerDetail::where('customer_id', $customer->id)->first();

        if (!$customerDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Customer details not found',
            ], 404);
        }

        $customerDetail->language = $request->language;
        $customerDetail->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferred language updated successfully',
        ], 200);
    }

    /**
     * Update the phone number of a customer.
     *
     * @param  Request  $request
     * @return JsonResponse
    */
    public function updatePhone(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $customerDetail = CustomerDetail::where('customer_id', $customer->id)->first();

        if (!$customerDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Customer details not found',
            ], 404);
        }

        $customerDetail->phone = $request->phone;
        $customerDetail->save();

        return response()->json([
            'success' => true,
            'message' => 'Phone number updated successfully',
            'data' => $customerDetail,
        ], 200);
    }

    /**
     * Update the email address of a customer.
     *
     * @param  Request  $request
     * @return JsonResponse
    */
    public function updateEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email address',
                'errors' => $validator->errors(),
            ], 400);
        }

        $customer = Auth::guard('customer')->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $customerDetail = CustomerDetail::where('customer_id', $customer->id)->first();

        if (!$customerDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Customer details not found',
            ], 404);
        }

        $customer->email = $request->email;
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Email updated successfully',
            'data' => $customer,
        ], 200);
    }

    /**
     * Update the password of a customer.
     *
     * @param  Request  $request
     * @return JsonResponse
    */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = Auth::user();
        $customer = $user->hasRole('Customer') ? $user : null;

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($request->new_password !== $request->confirm_new_password) {
            return response()->json([
                'success' => false,
                'message' => 'Passwords do not match',
            ], 400);
        }

        $customer->password = Hash::make($request->new_password);
        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ], 200);
    }
}
