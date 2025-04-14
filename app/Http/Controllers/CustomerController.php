<?php

  namespace App\Http\Controllers;

  use App\Enums\Permissions;
  use App\Helpers\CustomerHelper;
  use App\Http\Requests\CustomerRequest;
  use App\Interfaces\CustomerInterface;
  use App\Models\SurvivorNumber;
  use App\Models\User;
  use App\Repositories\CustomerRepository;
  use App\Services\CustomerService;
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
    protected CustomerService $customerService;

    public function __construct(CustomerRepository $customerRepository, CustomerService $customerService)
    {
      $this->customerRepository = $customerRepository;
      $this->customerService = $customerService;
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
        $this->customerService->updateCustomer($request, $user);

        return redirect()->route('customers.edit', $user->id)
          ->with('success', 'Customer updated successfully.');
      } catch (\Exception|\Throwable $e) {
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

    public function editBySurvivorNumber(string $survivorNumber)
    {
        if (!$survivorNumber) {
            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }

        $survivorNumberModel = SurvivorNumber::query()
            ->with(['user'])
            ->where('survivor_number', $survivorNumber)
            ->first();

        if (!$survivorNumberModel) {
            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }

        $customer = $survivorNumberModel->user;

        if (!$customer) {
            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }

        return $this->edit($customer);
    }
  }
