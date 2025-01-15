<?php

namespace App\Http\Controllers\Api;

use App\Enums\Permissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\ListMembersRequest;
use App\Http\Requests\Team\UpdateMemberRequest;
use App\Http\Requests\Team\UpdateMemberRoleRequest;
use App\Interfaces\TeamRepositoryInterface;
use App\Repositories\TeamRepository;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class TeamController extends Controller
{

    use HandlePermissions;
    use ExceptionLogger;

    protected TeamRepositoryInterface $teamRepositoryInterface;
    protected $organizationId;

    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepositoryInterface = $teamRepository;
        $this->organizationId = config('settings.organization_id');
    }

    public function listMembersByOrganization(ListMembersRequest $request)
    {
        try {
            return $this->withPermission([Permissions::ViewUsers], function ($request) {
                return $this->teamRepositoryInterface->getAllMembers($this->organizationId);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }
    public function updateMemberRoles(UpdateMemberRoleRequest $request)
    {
        try {
            return $this->withPermission([Permissions::EditUsers], function ($request) {
                $user_id = $request->user_id;
                $roles = $request->roles;
                return $this->teamRepositoryInterface->updateMemberRoles($user_id, $this->organizationId, $roles);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);
        }
    }
    public function updateMember(UpdateMemberRequest $request)
    {
        try {
            return $this->withPermission([Permissions::EditUsers], function ($request) {
                $data = $request->all();
                $this->teamRepositoryInterface->updateMember($data);
                return Redirect::route('teams', ['slug' => '70K'])->with('message', 'User updated successfully');
            }, $request);
        } catch (\Exception $ex) {
            return Inertia::render('Team', [
                'errors' => $ex->getMessage()
            ]);
        }
    }
}
