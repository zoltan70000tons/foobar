<?php

namespace App\Http\Controllers;

use App\Support\LogActions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Log as LogModel;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    public function __construct() {}

    public function index(Request $request)
    {
        $type   = $request->filled('type')   ? (string) $request->string('type')   : null; // booking|customer|cabin
        $action = $request->filled('action') ? (string) $request->string('action') : null; // code - action
        $from   = $request->date('from'); // Carbon|null
        $to     = $request->date('to');   // Carbon|null

        $perPage = $request->get('per_page') ?? 30;
        $page = $request->get('page');

        $q = LogModel::query()
            ->latest('logs.created_at') 
            ->when($type,   fn($qb) => $qb->where('logs.related_type', $type)) 
            ->when($action, fn($qb) => $qb->where('logs.action', $action))   
            ->when($from,   fn($qb) => $qb->where('logs.created_at', '>=', $from->copy()->startOfDay())) // 👈
            ->when($to,     fn($qb) => $qb->where('logs.created_at', '<=', $to->copy()->endOfDay()));    // 👈

        $logs = $q->select(
            'logs.id',
            'logs.created_at',
            'logs.actor_type',
            'logs.actor_id',
            'users.username as actor_username',
            'logs.action',
            'logs.description',
            'logs.related_type',
            'logs.related_id',
            'bookings.booking_code',
            'bookings.event_id'
        )
            ->leftJoin('users', 'users.id', '=', 'logs.actor_id')
            ->leftJoin('bookings', function ($join) {
                $join->on('logs.related_id', '=', DB::raw('bookings.id::text'));
                $join->where('logs.related_type', '=', 'booking');
            })
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();

        $actionsByType = LogActions::all();

        return Inertia::render('Log/Index', [
            'logs' => $logs,
            'filters' => [
                'type' => $type,
                'action' => $action,
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'actionsByType' => $actionsByType,
        ]);
    }
}
