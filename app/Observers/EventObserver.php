<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionEvent;
use App\Models\Event;
use App\Support\GlobalLogger;

class EventObserver
{
    public function created(Event $event): void
    {
        GlobalLogger::log(
            LogActionEvent::EVENT_CREATED,
            'event',
            $event->id,
            'Event created',
            [
                'after' => [
                    'name' => $event->name,
                    'description' => $event->description,
                    'image' => $event->image,
                    'status' => $event->status,
                    'organizationId' => $event->organization_id,
                    'address' => $event->address,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                ],
            ]
        );
    }

    public function updated(Event $event): void
    {
        GlobalLogger::log(
            LogActionEvent::EVENT_UPDATED,
            'event',
            $event->id,
            'Event updated',
            [
                'before' => [
                    'name' => $event->getOriginal('name'),
                    'description' => $event->getOriginal('description'),
                    'image' => $event->getOriginal('image'),
                    'status' => $event->getOriginal('status'),
                    'organizationId' => $event->getOriginal('organization_id'),
                    'address' => $event->getOriginal('address'),
                    'start_date' => $event->getOriginal('start_date'),
                    'end_date' => $event->getOriginal('end_date'),
                ],
                'after' => [
                    'name' => $event->name,
                    'description' => $event->description,
                    'image' => $event->image,
                    'status' => $event->status,
                    'organizationId' => $event->organization_id,
                    'address' => $event->address,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                ],
            ]
        );
    }

    public function deleted(Event $event): void
    {
        GlobalLogger::log(
            LogActionEvent::EVENT_DELETED,
            'event',
            $event->id,
            'Event deleted',
            [
                'before' => [
                    'name' => $event->name,
                    'description' => $event->description,
                    'image' => $event->image,
                    'status' => $event->status,
                    'organizationId' => $event->organization_id,
                    'address' => $event->address,
                    'start_date' => $event->start_date,
                    'end_date' => $event->end_date,
                ],
            ]
        );
    }
}
