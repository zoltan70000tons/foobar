<?php 
namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    public function __construct()
    {
        $user = auth()->user(); // O User::find($id) para un usuario específico
        $teams = $user->teams;
        dd($teams);
    }
    public function create()
    {
        return Inertia::render('InviteUserPage');
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|string',
        ])->validate();

        Invitation::create($validated);

        return redirect()->back()->with('success', 'Invitation sent successfully.');
    }
}