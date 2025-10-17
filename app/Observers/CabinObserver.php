<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionCabin;
use App\Models\Cabin;
use App\Support\GlobalLogger;

class CabinObserver
{
    public function updated(Cabin $cabin): void
    {
        // helpers
        $statusBefore = $cabin->getRawOriginal('status');           // string antes
        $statusAfter  = $cabin->status?->value ?? null;             // string después

        // inventory
        if ($cabin->wasChanged('inventory')) {
            GlobalLogger::log(
                LogActionCabin::INVENTORY_CHANGED,
                'cabin',
                $cabin->id,
                sprintf('Inventory %s → %s', $cabin->getRawOriginal('inventory'), $cabin->inventory),
                [
                    'before' => ['inventory' => $cabin->getRawOriginal('inventory')],
                    'after'  => ['inventory'  => (int) $cabin->inventory],
                ]
            );
        }

        // status
        if ($cabin->wasChanged('status')) {
            GlobalLogger::log(
                LogActionCabin::STATUS_CHANGED,
                'cabin',
                $cabin->id,
                sprintf('%s → %s', $statusBefore, $statusAfter),
                [
                    'before' => ['status' => $statusBefore],
                    'after'  => ['status' => $statusAfter],
                ]
            );
        }

        // category
        if ($cabin->wasChanged('cabin_category_id')) {
            GlobalLogger::log(
                LogActionCabin::CATEGORY_CHANGED,
                'cabin',
                $cabin->id,
                sprintf('Category %s → %s', $cabin->getRawOriginal('cabin_category_id'), $cabin->cabin_category_id),
                [
                    'before' => ['cabin_category_id' => $cabin->getRawOriginal('cabin_category_id')],
                    'after'  => ['cabin_category_id' => $cabin->cabin_category_id],
                ]
            );
        }

        // type
        if ($cabin->wasChanged('cabin_type_id')) {
            GlobalLogger::log(
                LogActionCabin::TYPE_CHANGED,
                'cabin',
                $cabin->id,
                sprintf('TYPE %s → %s', $cabin->getRawOriginal('cabin_type_id'), $cabin->cabin_type_id),
                [
                    'before' => ['cabin_type_id' => $cabin->getRawOriginal('cabin_type_id')],
                    'after'  => ['cabin_type_id' => $cabin->cabin_type_id],
                ]
            );
        }

        // notes
        if ($cabin->wasChanged('notes')) {
            GlobalLogger::log(
                LogActionCabin::NOTES_UPDATED,
                'cabin',
                $cabin->id,
                'Notes updated',
                [
                    'before' => ['notes' => $cabin->getRawOriginal('notes')],
                    'after'  => ['notes' => $cabin->notes],
                ]
            );
        }

        // internal notes
        if ($cabin->wasChanged('internal_notes')) {
            GlobalLogger::log(
                LogActionCabin::INTERNAL_NOTES_UPDATED,
                'cabin',
                $cabin->id,
                'Internal Notes Updated',
                [
                    'before' => ['internal_notes' => $cabin->getRawOriginal('internal_notes')],
                    'after'  => ['internal_notes' => $cabin->internal_notes],
                ]
            );
        }
    }
}
