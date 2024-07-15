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

public function __construct(TeamRepository $teamRepository) {
    $this->teamRepositoryInterface = $teamRepository;
}

public function listMembersByOrganization(ListMembersRequest $request){
    $org_id = $request->org_id;
    return $this->teamRepositoryInterface->getAllMembers($org_id);
}

public function updateMemberRoles(UpdateMemberRoleRequest $request){
    $org_id = $request->org_id;
    $user_id = $request->user_id;
    $roles = $request->roles;
    return $this->teamRepositoryInterface->updateMemberRoles($user_id, $org_id, $roles);
}
   
}
