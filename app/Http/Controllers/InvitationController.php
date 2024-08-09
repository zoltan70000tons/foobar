<?php 
namespace App\Http\Controllers;

use App\Interfaces\TeamRepositoryInterface;
use App\Repositories\TeamRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class InvitationController extends Controller
{
    private TeamRepositoryInterface $teamRepository;
    public function __construct(TeamRepository $teamRepository)
    {
        $user = auth()->user(); 
        $this->teamRepository = $teamRepository;
    }
    public function create()
    {
        return Inertia::render('InviteUserPage');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'invitations' => 'required|array',
            'invitations.*.email' => 'required|email',
            'invitations.*.role' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        foreach ($request->invitations as $invitation) {
            $this->teamRepository->inviteMember($invitation);
        }
    
        
        //return response()->json(['message' => 'Invitations sent successfully']);
        //Invitation::create($validated);

       // return redirect()->back()->with('success', 'Invitation sent successfully.');
    }
}