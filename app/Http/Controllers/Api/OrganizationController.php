<?php

namespace App\Http\Controllers\Api;

use App\Classes\ApiResponserHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\CreateOrganizationRequest;
use App\Http\Requests\Organization\ListOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Interfaces\OrganizationRepositoryInterface;
use App\Models\Organization;
use App\Repositories\OrganizationRepository;

class OrganizationController extends Controller
{

    private OrganizationRepositoryInterface $organizationRepositoryInterface;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepositoryInterface = $organizationRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ListOrganizationRequest $request)
    {
        return $this->organizationRepositoryInterface->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOrganizationRequest $request)
    {
        $data = $request->all();
        return $this->organizationRepositoryInterface->save($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
