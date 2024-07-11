<?php 
namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class ManageRoleController extends Controller
{
    public function __construct()
    {
    }
    public function index()
    {
        return Inertia::render('ManageRole');
    }

}