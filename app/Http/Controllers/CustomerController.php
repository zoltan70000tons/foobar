<?php

  namespace App\Http\Controllers;

  use App\Enums\Permissions;
  use App\Http\Requests\CustomerRequest;
  use App\Interfaces\CustomerInterface;
  use App\Models\User;
  use App\Repositories\CustomerRepository;
  use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function index(Request $request)
    {
      try {
        return $this->withPermission([Permissions::ViewCustomers], function () {
          return Inertia::render('Customer/Index', [
            'customers' => $this->customerRepository->getAllCustomerData(),
          ]);
        }, $request);
      } catch (\Exception $e) {
        $this->logException($e);

        return redirect()->route('customer.index')->with('error', 'Something went wrong.');
      }
    }

    public function getPaginated(Request $request): LengthAwarePaginator
    {
      $page = $request->get('page');
      $perPage = $request->get('per_page');
      $sortBy = $request->get('sort_by');
      $sortDir = $request->get('sort_direction');
      $filters = json_decode($request->get('filters'), true);

      return $this->customerRepository->getPaginatedCustomerData($page, $perPage, $sortBy, $sortDir, $filters);
    }

    public function create(): InertiaResponse
    {
      return Inertia::render('Customer/Create');
    }

    public function store(CustomerRequest $request): RedirectResponse|Response|InertiaResponse
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
      $bookings = $this->customerRepository->getBookingDataForCustomer($user);

      return Inertia::render('Customer/Edit', [
        'customer' => $user,
        'bookings' => $bookings,
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
        return $this->withPermission([Permissions::ViewCustomers], function ($user) {
          $user->load(['detail', 'survivorNumber', 'customerAddress', 'bookings']);
          $bookings = $this->customerRepository->getBookingDataForCustomer($user);

          return Inertia::render('Customer/View', [
            'customer' => $user,
            'bookings' => $bookings,
          ]);
        }, $user);
      } catch (\Exception $e) {
        $this->logException($e);

        return redirect()->route('customers.index')->with('error', 'Something went wrong.');
      }
    }

    public function destroy(User $user): RedirectResponse|Response|InertiaResponse
    {
      try {
        return $this->withPermission([Permissions::DeleteCustomers], function ($user) {
          $this->customerRepository->delete($user);

          return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
        }, $user);
      } catch (\Exception $e) {
        $this->logException($e);

        return redirect()->route('customer.index')->with('error', 'Something went wrong.');
      }
    }
  }
