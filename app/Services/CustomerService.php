<?php

namespace App\Services;

use App\Helpers\CustomerHelper;
use App\Http\Requests\CustomerRequest;
use App\Interfaces\CustomerInterface;
use App\Models\User;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

class CustomerService {
    protected CustomerInterface $customerRepository;

    public function __construct(CustomerRepository $customerRepository) {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @throws Throwable
     */
    public function updateCustomer(CustomerRequest $request, User $user): void {
        DB::beginTransaction();

        try {
            $this->customerRepository->update($request, $user);

            CustomerHelper::syncPassengerData($request, $user);

            DB::commit();
        } catch (\Exception | Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
