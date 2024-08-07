<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\ListMembersRequest;
use App\Http\Requests\Team\UpdateMemberRoleRequest;
use App\Interfaces\TeamRepositoryInterface;
use App\Repositories\TeamRepository;

class TeamController extends Controller
{
protected TeamRepositoryInterface $teamRepositoryInterface;
protected $organizationId;

public function __construct(TeamRepository $teamRepository) {
    $this->teamRepositoryInterface = $teamRepository;
    $this->organizationId = config('settings.organization_id');

}

public function listMembersByOrganization(ListMembersRequest $request){
    return $this->teamRepositoryInterface->getAllMembers($this->organizationId);
}

public function updateMemberRoles(UpdateMemberRoleRequest $request){
    $user_id = $request->user_id;
    $roles = $request->roles;
    return $this->teamRepositoryInterface->updateMemberRoles($user_id, $this->organizationId, $roles);
}
   
}
