<?php

namespace App\Repositories;

//use App\Classes\ApiResponserHelper;
use App\Http\Resources\OrganizationResource;
use App\Interfaces\OrganizationRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use App\Traits\JsonResponseTrait;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    use JsonResponseTrait;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {

    }

    public function getAll()
    {
        try {
            $organizations = Organization::all();
            return $this->successResponse($organizations, 'Organizations listed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to list organizations', 500, $e->getMessage());
        }
    }

    public function find($id)
    {
    }

    public function save($data)
    {
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $data['user_name'];
            $user->email = $data['email'];
            $user->password = bcrypt('password'); 
            $user->save();

            $org = new Organization();
            $org->name = $data['name'];
            $org->terms = $data['terms'] ?? 0;
            $org->save();
                       
            Role::create(['name' => 'admin', 'team_id' => $org->id]);
            $org->users()->attach($user->id);
            setPermissionsTeamId($org->id);
            $role = Role::findByName('admin','web');
            $user->assignRole($role);

            DB::commit();
            return $this->successResponse($org, 'Organization created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create organization', 500, $e->getMessage());
        }
    }

    public function update($data, $id)
    {
        
    }

    public function delete($id)
    {
    }
}
