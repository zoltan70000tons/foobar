<?php

namespace App\Repositories;

use App\Interfaces\TeamRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TeamRepository implements TeamRepositoryInterface
{

    use JsonResponseTrait;
    
    protected $organizationId;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->organizationId = config('settings.organization_id');
        setPermissionsTeamId($this->organizationId);
    }

    public function getAllMembers($org_id, $team = null)
    {
        setPermissionsTeamId($org_id);
        $org = Organization::find($org_id);
        if ($org) {
            $members = User::where(['organization_id' => $this->organizationId])->get();
            $result = $members->map(function ($user) use ($org) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_name' => $user->username,
                    'email' => $user->email,
                    'status' => $user->user_activated_at ? 'Active' : 'Pending',
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'organization_id' => $org->id,
                    'organization_name' => $org->name,
                    'survivor_number' => $user->survivor_number
                ];
            });

            return $result;
        }
        return null;
    }

    public function findMember($team, $id)
    {
    }

    public function updateMemberRoles($user_id, $org_id, $roles)
    {
        try {
            $currentUser = Auth::user();
            if (count($roles) > 1) {
                throw new \Exception('It is not allowed to have more than one role');
            }
            setPermissionsTeamId($org_id);
            $user = User::find($user_id);
            $user->syncRoles($roles);
            return $this->successResponse($roles, 'Roles updated successfully');
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage());
        }
    }

    public function inviteMember($data)
    {
       try {
        $email = $data['email'];
        $role = $data['role'];
        $user = User::where('email', $email)->first();
        if(!$user) {
            $user = User::create([
                'username' => $email,
                'email' => $email,
                'password' => Hash::make(Str::password()),
                'organization_id' => $this->organizationId
            ]);
            $token = Str::random(60);
            $domain = config('settings.application_url');
            $link = $domain . '/join-organization';
            $url = url($link) . '?token=' . $token;
            $roles[] = $role;
            $user->syncRoles($roles);
            // save in database ????
            $url = URL::temporarySignedRoute('organization.join', now()->addWeek(), ['user' => $user->id]);
            Mail::to($email)->send(new \App\Mail\InviteUserMail($url));
        }
       } catch (\Exception $e) {

        return $this->errorResponse($e->getMessage());
       }
    }
}
