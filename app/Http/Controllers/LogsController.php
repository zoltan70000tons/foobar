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
        $type = $request->filled('type') ? (string) $request->string('type') : null;
        $action = $request->filled('action') ? $request->array('action') : [];
        $from = $request->date('from');
        $to = $request->date('to');
        $bookingCode = $request->filled('bookingCode') ? $request->string('bookingCode') : null;
        $agentName = $request->filled('agentName') ? $request->string('agentName') : null;
        $actorType = $request->input('actorType');

        if (is_string($actorType)) {
            $actorType = [$actorType];
        } elseif (!is_array($actorType) || empty($actorType)) {
            $actorType = ['agent', 'system'];
        }

        $perPage = $request->get('per_page') ?? 30;
        $page = $request->get('page');

        $q = LogModel::query()
            ->latest('logs.created_at') 
            ->when($type, fn($qb) => $qb->where('logs.related_type', $type))
            ->when(!empty($action), fn($qb) => $qb->whereIn('logs.action', $action))
            ->when($from, fn($qb) => $qb->where('logs.created_at', '>=', $from->copy()->startOfDay()))
            ->when($to, fn($qb) => $qb->where('logs.created_at', '<=', $to->copy()->endOfDay()))
            ->when($bookingCode, fn($qb) => $qb->where('bookings.booking_code', 'ILIKE', "%{$bookingCode}%"))
            ->when($agentName, fn($qb) => $qb->where('users.username', 'ILIKE', "%{$agentName}%"))
            ->when($actorType, fn($qb) => $qb->whereIn('logs.actor_type', $actorType));

        $logs = $q->select(
            'logs.id',
            'logs.created_at',
            'logs.actor_type',
            'logs.actor_id',
            'users.username as actor_username',
            'users.email as actor_email',
            'logs.action',
            'logs.description',
            'logs.related_type',
            'logs.related_id',
            'logs.payload',
            'bookings.booking_code',
            'bookings.event_id',
            'cabin_specs.cabin_number',
        )
            ->leftJoin('users', 'users.id', '=', 'logs.actor_id')
            ->leftJoin('bookings', function ($join) {
                $join->on('logs.related_id', '=', DB::raw('bookings.id::text'));
                $join->where('logs.related_type', '=', 'booking');
            })
            ->leftJoin('cabins', function ($join) {
                $join->on('logs.related_id', '=', DB::raw('cabins.id::text'));
                $join->where('logs.related_type', '=', 'cabin');
            })
            ->leftJoin('cabin_specs', function ($join) {
                $join->on('cabins.cabin_spec_id', '=', 'cabin_specs.id');
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
