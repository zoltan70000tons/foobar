<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Models\PotentialSurvivorMatch;
use App\Models\Passenger;
use App\Models\UserDetail;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\ExceptionLogger;
use App\Traits\HandlePermissions;
use Illuminate\Http\RedirectResponse;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PotentialSurvivorMatchesController extends Controller
{
    use HandlePermissions;
    use ExceptionLogger;

    public function index(Request $request)
    {
        try {
            return $this->withPermission([Permissions::ViewCustomers], function () {
                return Inertia::render('PotentialSurvivorMatches/Index', [
                    'potentialMatches' => PotentialSurvivorMatch::all(),
                    'statuses' => ['in_progress', 'resolved'],
                ]);
            }, $request);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('matches.index')->with('error', 'Something went wrong.');
        }
    }

    public function getPaginated(Request $request): LengthAwarePaginator
    {
        $page = $request->get('page');
        //Adding 1 to $page, since frontend starts to index it from 0, and Laravel expects it from 1
        $page++;
        $perPage = $request->get('per_page');
        $sortBy = $request->get('sort_by');
        $sortDir = $request->get('sort_direction');
        $filters = json_decode($request->get('filters'), true) ?? [];
        $selectedStatuses = $request->get('statuses');

        $baseQuery = PotentialSurvivorMatch::query();
        if ($selectedStatuses) {
            $baseQuery = $baseQuery->whereIn('status', explode(',', $selectedStatuses));
        }
        foreach ($filters as $key => $value) {
            if ($key === 'passenger_dob') {
                $baseQuery->whereRaw("TO_CHAR(passenger_dob, 'YYYY-MM-DD') ILIKE ?", ["%{$value}%"]);
            } elseif ($key === 'user_dob') {
                $baseQuery->whereRaw("TO_CHAR(user_dob, 'YYYY-MM-DD') ILIKE ?", ["%{$value}%"]);
            } else {
                $columnMap = [
                    'passenger_first_name' => 'passenger_first_name',
                    'passenger_last_name' => 'passenger_last_name',
                    'passenger_dob' => 'passenger_dob',
                    'user_first_name' => 'user_first_name',
                    'user_last_name' => 'user_last_name',
                    'user_dob' => 'user_dob',
                ];
                if (isset($columnMap[$key])) {
                    $baseQuery->whereRaw("{$columnMap[$key]} ILIKE ?", ["%{$value}%"]);
                }
            }
        }

        return $baseQuery
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function update(int $id)
    {
        try {
            $reviewer = Auth::user();

            $potentialSurvivorMatch = PotentialSurvivorMatch::find($id);
            $passenger = $potentialSurvivorMatch->passenger;
            $userDetails = $potentialSurvivorMatch->userDetail;
            $user = $userDetails->user;
            $survivorNumber = $user->survivorNumber;

            $passenger->survivor_number = $survivorNumber->survivor_number;
            $passenger->save();

            $potentialSurvivorMatch->review_date = Carbon::now();
            $potentialSurvivorMatch->updated_at = Carbon::now();
            $potentialSurvivorMatch->reviewer_id = $reviewer->id;
            $potentialSurvivorMatch->status = 'approved';
            $potentialSurvivorMatch->save();

            return redirect()->route('matches.index')
                ->with('success', 'Match updated successfully.');
        } catch (\Exception|\Throwable $e) {
            $this->logException($e);
            return redirect()->route('matches.show', ['id' => $id])->with('error', 'Problem updating potential match.');
        }
    }

    public function resolve(int $id)
    {
        try {
            $reviewer = Auth::user();

            $potentialSurvivorMatch = PotentialSurvivorMatch::find($id);
            $potentialSurvivorMatch->review_date = Carbon::now();
            $potentialSurvivorMatch->updated_at = Carbon::now();
            $potentialSurvivorMatch->reviewer_id = $reviewer->id;
            $potentialSurvivorMatch->status = 'resolved';
            $potentialSurvivorMatch->save();

            return redirect()->route('matches.index')
                ->with('success', 'Match resolved successfully.');
        } catch (\Exception|\Throwable $e) {
            $this->logException($e);
            return redirect()->route('matches.show', ['id' => $id])->with('error', 'Problem resolving double booking.');
        }
    }

    public function show(int $id): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::ViewCustomers], function ($id) {
                $potentialSurvivorMatch = PotentialSurvivorMatch::find($id);
                $passenger = $potentialSurvivorMatch->passenger;

                $otherPassenger = null;
                if ($potentialSurvivorMatch->type === 'double_booking') {
                    $userDetail = $potentialSurvivorMatch->userDetail;
                    $otherUser = $userDetail->user;
                    $otherSurvivorNumber = $otherUser->survivorNumber;
                    $otherPassenger = Passenger::query()
                        ->where('survivor_number', $otherSurvivorNumber->survivor_number)
                        ->with([
                            'booking' => function ($q) {
                                $q->where('status', '!=', 'CANCELLED')
                                    ->whereHas('event', function ($q2) {
                                        $q2->where('status', '!=', 'CLOSED');
                                    })
                                    ->with('event');
                            },
                        ])
                        ->first();
                }

                return Inertia::render('PotentialSurvivorMatches/View', [
                    'potentialMatch' => $potentialSurvivorMatch,
                    'booking' => $passenger->booking,
                    'event' => $passenger->booking->event,
                    'otherPassenger' => $otherPassenger,
                ]);
            }, $id);
        } catch (\Exception $e) {
            $this->logException($e);

            return redirect()->route('matches.index')->with('error', 'Something went wrong.');
        }
    }

    public function destroy(int $id): RedirectResponse|Response|InertiaResponse
    {
        try {
            return $this->withPermission([Permissions::DeleteCustomers], function ($id) {
                $reviewer = Auth::user();
                $potentialSurvivorMatch = PotentialSurvivorMatch::find($id);
                $passenger = $potentialSurvivorMatch->passenger;

                $passenger->survivor_sync_attempts = 3;
                $passenger->save();

                $potentialSurvivorMatch->status = 'rejected';
                $potentialSurvivorMatch->review_date = Carbon::now();
                $potentialSurvivorMatch->updated_at = Carbon::now();
                $potentialSurvivorMatch->reviewer_id = $reviewer->id;
                $potentialSurvivorMatch->save();

                return redirect()->route('matches.index')->with('success', 'Potential match deleted successfully.');
            }, $id);
        } catch (\Exception $e) {
            $this->logException($e);
            return redirect()->route('matches.index')->with('error', 'Something went wrong.');
        }
    }

    public function syncSurvivors()
    {
        $exitCode = Artisan::call('survivors:sync --only-summary');
        $output = Artisan::output();

        return response()->json([
            'status' => $exitCode,
            'output' => $output,
        ]);
    }
}
