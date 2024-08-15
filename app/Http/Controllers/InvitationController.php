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

        $successfulInvitations = [];
        $failedInvitations = [];
    
        foreach ($request->invitations as $invitation) {
            $result = $this->teamRepository->inviteMember($invitation);
    
            if ($result) {
                $successfulInvitations[] = $invitation;
            } else {
                $failedInvitations[] = $invitation;
            }
        }
    
        if (count($failedInvitations) > 0) {
            return response()->json([
                'message' => 'Some invitations could not be processed.'
            ], 500);
        }
    
        return response()->json([
            'message' => 'All invitations were processed successfully.'
        ], 200);
    
    }
}