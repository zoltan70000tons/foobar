<?php

namespace App\Repositories;

use App\Interfaces\TeamRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserDetail;
use App\Traits\JsonResponseTrait;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
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
            $members = User::with(['detail', 'roles'])
                ->where('organization_id', $this->organizationId)
                ->whereNotIn('username', ['SuperAdmin','admin', 'system'])
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'Customer');
                })
                ->get();
    
            $result = $members->map(function ($user) use ($org) {
                $firstName = $user->detail->first_name ?? ''; // Default to an empty string if null
                $lastName = $user->detail->last_name ?? '';  // Default to an empty string if null
                $fullName = trim("$firstName $lastName");    // Combine and trim
    
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'user_name' => $user->username,
                    'email' => $user->email,
                    'status' => $user->email_verified_at ? 'Active' : 'Pending',
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'organization_id' => $org->id,
                    'organization_name' => $org->name,
                    'survivor_number' => $user->survivor_number,
                    'detail' => $user->detail,
                    'fullName' => $fullName, // Add fullName here
                ];
            });
            return $result;
        }
        return null;
    }
    

    public function findMember($team, $id) {}

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
           // return $this->successResponse($roles, 'Roles updated successfully');
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
            if (!$user) {
                $user = User::create([
                    'username' => $email,
                    'email' => $email,
                    'password' => Hash::make(Str::password()),
                    'organization_id' => $this->organizationId
                ]);
                $roles[] = $role;
                $user->syncRoles($roles);
                $url = URL::temporarySignedRoute('organization.join', now()->addWeek(), ['user' => $user->id, 'org' => $this->organizationId]);
                Mail::to($email)->send(new \App\Mail\InviteUserMail($url));
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateMember($data)
    {
        try {
            $email = $data['email'];
            $user = User::where('email', $email)->first();

            if ($user) {
                $user_id = $user->id;
                $detailsData = /*array_filter(*/[
                    'user_id' => $user_id,
                    'first_name' => $data['firstname'] ?? null,
                    'last_name' => $data['lastname'] ?? null,
                    'middle_name' => $data['middlename'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone_number'] ?? null,
                ]/*, function ($value) {
                    return !is_null($value) && $value !== '';
                })*/;

                if (!empty($detailsData)) {
                    UserDetail::updateOrCreate(
                        ['user_id' => $user_id],
                        $detailsData
                    );
                    return Redirect::back()->with('success', 'Member details updated successfully.');
                } else {
                    return Redirect::back()->with('info', 'No data provided for update.');
                }
            } else {
                return Redirect::back()->with('error', 'User not found.');
            }
        } catch (\Exception $ex) {
            return Redirect::back()->with('error', $ex->getMessage());
        }
    }
}
