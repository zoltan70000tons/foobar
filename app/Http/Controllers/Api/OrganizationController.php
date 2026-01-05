<?php

namespace App\Http\Controllers\Api;

use App\Classes\ApiResponserHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\CreateOrganizationRequest;
use App\Http\Requests\Organization\ListOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Interfaces\OrganizationRepositoryInterface;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\OrganizationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class OrganizationController extends Controller {
    private OrganizationRepositoryInterface $organizationRepositoryInterface;

    public function __construct(OrganizationRepository $organizationRepository) {
        $this->organizationRepositoryInterface = $organizationRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(ListOrganizationRequest $request) {
        return $this->organizationRepositoryInterface->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateOrganizationRequest $request) {
        $data = $request->all();
        return $this->organizationRepositoryInterface->save($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update() {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        //
    }
    public function join(Request $request) {
        if ($request->isMethod('get')) {
            if (!$request->hasValidSignature()) {
                return 'NOT VALID REQUEST';
            }

            $userId = $request->query('user');
            $user = User::findOrFail($userId);
            $email = $user->email;

            return Inertia::render('JoinOrganization', [
                'email' => $email,
            ]);
        }

        if ($request->isMethod('put')) {
            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'user_nickname' => 'required|string|max:255',
                'password' => 'required|string|max:255',
                'user_name' => 'required|string|max:255',
                'user_lastname' => 'required|string|max:255',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if ($user) {
                try {
                    // Set user identity
                    $user->username = $validated['user_nickname'];
                    $user->email_verified_at = now();
                    $user->password = Hash::make($validated['password']);
                    $user->save();

                    // Set user details
                    UserDetail::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'first_name' => $validated['user_name'],
                            'last_name' => $validated['user_lastname'],
                            'language' => 'en', //FIXME
                        ],
                    );

                    // Redirect to login page
                    return redirect('/login')->with('status', 'Your account has been activated. You can login now.');
                } catch (\Exception $e) {
                    return back()->withErrors([
                        'error' => 'There was an issue processing your request. Please try again.',
                    ]);
                }
            } else {
                return back()->withErrors(['error' => 'User not found. Please contact support.']);
            }
        }
    }
}
