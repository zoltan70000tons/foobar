<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\ListMembersRequest;
use App\Http\Requests\Team\UpdateMemberRequest;
use App\Http\Requests\Team\UpdateMemberRoleRequest;
use App\Interfaces\TeamRepositoryInterface;
use App\Repositories\TeamRepository;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

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
public function updateMember(UpdateMemberRequest $request){
        try {
            $data = $request->all();
            $this->teamRepositoryInterface->updateMember($data);
            return Redirect::route('teams',['slug' => '70K'])->with('message', 'User updated successfully');
        } catch (\Exception $ex) {
            return Inertia::render('Team', [
                'errors' => $ex->getMessage()]);
        }
        
}
   
}
