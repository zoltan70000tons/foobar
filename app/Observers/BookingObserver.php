<?php

// app/Observers/BookingObserver.php
namespace App\Observers;

use App\Enums\GlobalLog\LogActionBooking;
use App\Models\Booking;
use App\Models\Cabin;
use App\Support\GlobalLogger;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        GlobalLogger::log(
            LogActionBooking::BOOKING_CREATED,
            'booking',
            $booking->id,
            sprintf('Booking created%s', $booking->booking_code ? " ({$booking->booking_code})" : ''),
            [
                'after' => [
                    'booking_code' => $booking->booking_code,
                    'booking_request_id' => $booking->booking_request_id,
                    'request_id' => $booking->request_id,
                    'event_id' => $booking->event_id,
                    'payment_plan' => $booking->payment_plan,
                    'customer_id' => $booking->customer_id,
                    'cabin_id' => $booking->cabin_id,
                    'is_single_occupancy' => $booking->is_single_occupancy,
                    'status' => $booking->status,
                    'bed_config' => $booking->bed_config,
                    'agent_id' => $booking->agent_id ?? null,
                ],
            ]
        );
    }

    public function updated(Booking $booking): void
    {

        // booking_code (string)
        if ($booking->wasChanged('booking_code')) {
            GlobalLogger::log(
                LogActionBooking::BOOKING_CODE_CHANGED,
                'booking',
                $booking->id,
                "Booking code {$booking->getOriginal('booking_code')} → {$booking->booking_code}",
                [
                    'before' => ['booking_code' => $booking->getOriginal('booking_code')],
                    'after' => ['booking_code' => $booking->booking_code],
                ]
            );
        }

        // booking_request_id (string)
        if ($booking->wasChanged('booking_request_id')) {
            GlobalLogger::log(
                LogActionBooking::BOOKING_REQUEST_ID_CHANGED,
                'booking',
                $booking->id,
                "Booking request ID {$booking->getOriginal('booking_request_id')} → {$booking->booking_request_id}",
                [
                    'before' => ['booking_request_id' => $booking->getOriginal('booking_request_id')],
                    'after' => ['booking_request_id' => $booking->booking_request_id],
                ]
            );
        }

        // request_id (string)
        if ($booking->wasChanged('request_id')) {
            GlobalLogger::log(
                LogActionBooking::REQUEST_ID_CHANGED,
                'booking',
                $booking->id,
                "Request ID {$booking->getOriginal('request_id')} → {$booking->request_id}",
                [
                    'before' => ['request_id' => $booking->getOriginal('request_id')],
                    'after' => ['request_id' => $booking->request_id],
                ]
            );
        }

        // event_id (int)
        if ($booking->wasChanged('event_id')) {
            GlobalLogger::log(
                LogActionBooking::EVENT_ID_CHANGED,
                'booking',
                $booking->id,
                "Event ID {$booking->getOriginal('event_id')} → {$booking->event_id}",
                [
                    'before' => ['event_id' => $booking->getOriginal('event_id')],
                    'after' => ['event_id' => $booking->event_id],
                ]
            );
        }

        // payment_plan (string)
        if ($booking->wasChanged('payment_plan')) {
            GlobalLogger::log(
                LogActionBooking::PAYMENT_PLAN_CHANGED,
                'booking',
                $booking->id,
                "Payment plan {$booking->getOriginal('payment_plan')} → {$booking->payment_plan}",
                [
                    'before' => ['payment_plan' => $booking->getOriginal('payment_plan')],
                    'after' => ['payment_plan' => $booking->payment_plan],
                ]
            );
        }

        // customer_id (uuid)
        if ($booking->wasChanged('customer_id')) {
            GlobalLogger::log(
                LogActionBooking::CUSTOMER_ID_CHANGED,
                'booking',
                $booking->id,
                "Customer ID {$booking->getOriginal('customer_id')} → {$booking->customer_id}",
                [
                    'before' => ['customer_id' => $booking->getOriginal('customer_id')],
                    'after' => ['customer_id' => $booking->customer_id],
                ]
            );
        }

        // cabin_id (int)
        if ($booking->wasChanged('cabin_id')) {
            $changeLog = self::generateBookingChangeLog($booking);

            $oldCabin = Cabin::query()->find($booking->getOriginal('cabin_id'));
            $oldCabinCategory = $oldCabin->category;
            $oldCabinCategorySpec = $oldCabinCategory->spec;
            $changeLog['before']['categoryCode'] = $oldCabinCategorySpec->category_code;
            $newCabin = Cabin::query()->find($booking->cabin_id);
            $newCabinCategory = $newCabin->category;
            $newCabinCategorySpec = $newCabinCategory->spec;
            $changeLog['after']['categoryCode'] = $newCabinCategorySpec->category_code;

            GlobalLogger::log(
                LogActionBooking::CABIN_ID_CHANGED,
                'booking',
                $booking->id,
                "Cabin ID {$booking->getOriginal('cabin_id')} → {$booking->cabin_id}, category code {$oldCabinCategorySpec->category_code} → {$newCabinCategorySpec->category_code}",
                [
                    'after' => $changeLog['after'],
                    'before' => $changeLog['before'],
                ]
            );
        }

        // is_single_occupancy (bool)
        if ($booking->wasChanged('is_single_occupancy')) {
            GlobalLogger::log(
                LogActionBooking::SINGLE_OCCUPANCY_CHANGED,
                'booking',
                $booking->id,
                "Single occupancy {$booking->getOriginal('is_single_occupancy')} → {$booking->is_single_occupancy}",
                [
                    'before' => ['is_single_occupancy' => $booking->getOriginal('is_single_occupancy')],
                    'after' => ['is_single_occupancy' => $booking->is_single_occupancy],
                ]
            );
        }

        // status (string)
        if ($booking->wasChanged('status')) {
            GlobalLogger::log(
                LogActionBooking::STATUS_CHANGED,
                'booking',
                $booking->id,
                "Status {$booking->getOriginal('status')} → {$booking->status}", //Doesn't work, because of early refresh
                [
                    'before' => ['status' => $booking->getOriginal('status')],
                    'after' => ['status' => $booking->status],
                ]
            );
            // Additional log if status changed to CANCELLED
            if ($booking->status === 'CANCELLED') {
                GlobalLogger::log(LogActionBooking::BOOKING_CANCELLED, 'booking', $booking->id, 'Booking was cancelled', [
                    'before' => ['status' => $booking->getOriginal('status')],
                    'after' => ['status' => $booking->status],
                ]);
            }
        }

        // bed_config (string)
        if ($booking->wasChanged('bed_config')) {
            GlobalLogger::log(
                LogActionBooking::BED_CONFIG_CHANGED,
                'booking',
                $booking->id,
                "Bed config {$booking->getOriginal('bed_config')} → {$booking->bed_config}",
                [
                    'before' => ['bed_config' => $booking->getOriginal('bed_config')],
                    'after' => ['bed_config' => $booking->bed_config],
                ]
            );
        }

        // agent_id (int)
        if ($booking->wasChanged('agent_id')) {
            GlobalLogger::log(
                LogActionBooking::AGEND_ID_CHANGED,
                'booking',
                $booking->id,
                "Agent ID {$booking->getOriginal('agent_id')} → {$booking->agent_id}",
                [
                    'before' => ['agent_id' => $booking->getOriginal('agend_id')],
                    'after' => ['agent_id' => $booking->agent_id],
                ]
            );
        }
    }

    public function deleted(Booking $booking): void
    {
        GlobalLogger::log(
            LogActionBooking::BOOKING_DELETED,
            'booking',
            $booking->id,
            sprintf('Booking deleted%s', $booking->booking_code ? " ({$booking->booking_code})" : ''),
            [
                'before' => [
                    'booking_code' => $booking->getOriginal('booking_code') ?? $booking->booking_code,
                    'booking_request_id' => $booking->getOriginal('booking_request_id') ?? $booking->booking_request_id,
                    'request_id' => $booking->getOriginal('request_id') ?? $booking->request_id,
                    'event_id' => $booking->getOriginal('event_id') ?? $booking->event_id,
                    'payment_plan' => $booking->getOriginal('payment_plan') ?? $booking->payment_plan,
                    'customer_id' => $booking->getOriginal('customer_id') ?? $booking->customer_id,
                    'cabin_id' => $booking->getOriginal('cabin_id') ?? $booking->cabin_id,
                    'is_single_occupancy' => $booking->getOriginal('is_single_occupancy') ?? $booking->is_single_occupancy,
                    'status' => $booking->getOriginal('status') ?? $booking->status,
                    'bed_config' => $booking->getOriginal('bed_config') ?? $booking->bed_config,
                    'agent_id' => $booking->getOriginal('agent_id') ?? ($booking->agend_id ?? null),
                ],
            ]
        );
    }

    public static function generateBookingChangeLog(Booking $booking): array
    {
        $fieldsToTrack = [
            'booking_request_id',
            'booking_code',
            'event_id',
            'customer_id',
            'payment_plan',
            'cabin_id',
            'is_single_occupancy',
            'bed_config',
            'agent_id',
            'status',
        ];

        $changes = [];
        $before = [];
        $after = [];

        foreach ($fieldsToTrack as $field) {
            $old = $booking->getOriginal($field);
            $new = $booking->$field;

            // Compare JSON fields safely
            if (is_array($old) || is_array($new)) {
                $old = json_encode($old);
                $new = json_encode($new);
            }

            if ($old != $new) {
                $before[$field] = $old;
                $after[$field] = $new;
            }
        }

        $changes['before'] = $before;
        $changes['after'] = $after;

        return $changes;
    }
}
