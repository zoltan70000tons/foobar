<?php

namespace App\Repositories;

use App\Interfaces\LogInterface;
use App\Models\Booking;
use App\Models\Comment;
use App\Models\Log;
use App\Models\UserDetail;
use Illuminate\Support\Collection;

class LogRepository implements LogInterface
{

    protected Booking $booking;

    public function getLogsByBookingId(int $bookingId): Collection
    {
        return Log::with('actor')->where('related_id', $bookingId)
            ->where('related_type', 'booking')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCommentsById(int $bookingId): Collection
    {
        return Comment::with('user')->where('booking_id', $bookingId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getHistory(int $bookingId): Collection
    {
        $this->booking = Booking::find($bookingId);

        $logs = $this->getLogsByBookingId($bookingId)->map(function ($l) {
            return [
                'id'         => 'log-'.$l->id,
                'type'       => 'log',
                'message'    => $l->description ?? $l->message ?? null,
                'action'     => $l->action ?? null,
                'user_id'    => $l->user_id ?? null,
                'payload'     => $l->payload ?? null,
                'booking_code' => $this->booking->booking_code,
                'status'     => $this->booking->status,
                'payment_plan' => $this->booking->payment_plan,
                'cabin_number' => $this->booking->cabin->cabin_number,
                'cabin_status' => $this->booking->cabin->status->value,
                'bed_config'   => $this->booking->bed_config,
                'is_single_occupancy' => $this->booking->is_single_occupancy,
                'booking_request_id' => $this->booking->booking_request_id,
                'actor'      => $l->actor ? [
                    'username' => $l->actor->username,
                    'avatar'   => $l->actor->detail ? $l->actor->detail->avatar : $this->noDetail(),
                ] : null,
                'created_at' => $l->created_at,
                'raw'        => $l->only(['id','related_id','related_type']), 
            ];
        });


        $comments = $this->getCommentsById($bookingId)->map(function ($c) {
            return [
                'id'         => 'comment-'.$c->id, 
                'type'       => 'comment',
                'message'    => $c->comment ?? $c->body ?? null,
                'action'     => 'New Comment',
                'user_id'    => $c->user_id ?? null,
                'payload'     => null,
                'booking_code' => $this->booking->booking_code,
                'status'     => $this->booking->status,
                'payment_plan' => $this->booking->payment_plan,
                'cabin_number' => $this->booking->cabin->cabin_number,
                'cabin_status' => $this->booking->cabin->status->value,
                'bed_config'   => $this->booking->bed_config,
                'is_single_occupancy' => $this->booking->is_single_occupancy,
                'booking_request_id' => $this->booking->booking_request_id,
                'actor'      => $c->user ? [
                    'username' => $c->user->username,
                    'avatar'   => $c->user->detail ? $c->user->detail->avatar : $this->noDetail(),
                ] : null,
                'created_at' => $c->created_at,
                'raw'        => $c->only(['id','booking_id']), 
            ];
        });

        return $logs->concat($comments)
                ->sortByDesc('created_at')
                ->values(); 
    }

    public function writeOnBooking($id, $action, $user)
    {
       
    }


    private function noDetail(){
        $payload = [
                        'image' => null,
                        'badge' => [
                            'text' => UserDetail::DEFAULT_BADGE_TEXT,
                            'background' => UserDetail::DEFAULT_BADGE_BACKGROUND,
                        ],
        ];
        return $payload;
    }
}
