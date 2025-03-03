<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Helpers\CustomerHelper;
use App\Http\Requests\CustomerCreateRequest;
use App\Http\Requests\CustomerRequest;
use App\Interfaces\CustomerInterface;
use App\Models\User;
use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Response as InertiaResponse;

class CustomerController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

    protected CustomerInterface $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index(Request $request): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::ViewUsers], function () {
                $customers = $this->customerRepository->getAllCustomerData();

                return Inertia::render('Customer/Index', [
                    'customers' => $customers,
                ]);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Customer/Create');
    }

    public function store(CustomerCreateRequest $request): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::CreateUsers], function ($request) {
                $this->customerRepository->store($request);

                return redirect()->route('customers.index')->with('flash', 'Customer created successfully.');
            }, $request);
        } catch (\Exception $e) {
            return redirect()->route('customers.index')->with('error', 'Problem creating customer.');
        }
    }

    public function edit(User $user): InertiaResponse
    {
        $user->load(['detail', 'survivorNumber', 'customerAddress']);

        return Inertia::render('Customer/Edit', [
            'customer' => $user,
        ]);
    }

    public function update(CustomerRequest $request, User $user)
    {
        try {
            $this->customerRepository->update($request, $user);

            return redirect()->route('customers.edit', $user->id)
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('customers.edit', $user->id)->with('error', 'Problem updating customer.');
        }
    }

    public function show(User $user): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::ViewUsers], function ($user) {
                $user->load(['detail', 'survivorNumber', 'customerAddress']);

                return Inertia::render('Customer/View', ['customer' => $user]);
            }, $user);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }
    }

    public function destroy(User $user): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::DeleteCustomers], function ($user) {
                $user->delete();

                return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
            }, $user);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }
    }
}
