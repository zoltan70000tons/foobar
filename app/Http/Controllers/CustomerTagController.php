<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Http\Requests\CustomerTagRequest;
use App\Interfaces\CustomerTagInterface;
use App\Models\UserTag;
use App\Repositories\CustomerTagRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Inertia\Response as InertiaResponse;

class CustomerTagController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

    protected CustomerTagInterface $customerRepository;

    public function __construct(CustomerTagRepository $customerTagRepository)
    {
        $this->customerTagRepository = $customerTagRepository;
    }

    public function index(Request $request): InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::ViewCustomerTags], function () {
                return Inertia::render('CustomerTag/Index', [
                    'tags' => $this->customerTagRepository->getAll(),
                ]);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer.index')->with('error', 'Something went wrong.');
        }
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('CustomerTag/Create');
    }

    public function store(CustomerTagRequest $request): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::CreateCustomerTags], function ($request) {
                $this->customerTagRepository->store($request);

                return redirect()->route('customer-tags.index')->with('flash', 'Customer tag created successfully.');
            }, $request);
        } catch (\Exception $e) {
            return redirect()->route('customer-tags.index')->with('error', 'Problem creating customer tag.');
        }
    }

    public function edit(UserTag $userTag): InertiaResponse
    {
        return Inertia::render('CustomerTag/Edit', [
            'userTag' => $userTag,
        ]);
    }

    public function update(CustomerTagRequest $request, UserTag $userTag)
    {
        try {
            return $this->withPermission([Permissions::EditCustomerTags], function ($request, $userTag) {
                $this->customerTagRepository->update($request, $userTag);

                return redirect()->route('customer-tags.edit', $userTag->id)
                    ->with('success', 'Customer tag updated successfully.');
            }, $request, $userTag);
        } catch (\Exception|\Throwable $e) {
            return redirect()->route('customer-tags.edit', $userTag->id)->with('error', 'Problem updating customer tag.');
        }
    }

    public function show(UserTag $userTag)
    {
        try {
            return $this->withPermission([Permissions::ViewCustomerTags], function ($userTag) {
                return Inertia::render('CustomerTag/View', [
                    'userTag' => $userTag,
                ]);
            }, $userTag);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer-tags.index')->with('error', 'Something went wrong.');
        }
    }

    public function destroy(UserTag $userTag): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::DeleteCustomerTags], function ($userTag) {
                $this->customerTagRepository->delete($userTag);

                return redirect()->route('customer-tags.index')->with('success', 'Customer tag deleted successfully.');
            }, $userTag);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('customer-tags.index')->with('error', 'Something went wrong.');
        }
    }
}
