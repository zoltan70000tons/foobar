<?php 
namespace App\Repositories;

use Illuminate\Support\Facades\Auth;

class UserRepository
{
    public function getCurrentUser()
    {
        return Auth::user();
    }

    public function someOtherMethod()
    {
        $currentUser = $this->getCurrentUser();
    }
}