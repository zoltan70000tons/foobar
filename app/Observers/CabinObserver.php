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
        $map = [
            'AVAILABLE' => 'PUBLICLY AVAILABLE',
            'RESERVED' => 'INTERNALLY AVAILABLE',
        ];

        $statusBefore = $map[$cabin->getRawOriginal('status')] ?? $cabin->getRawOriginal('status');
        $statusAfter  = $map[$cabin->status?->value ?? null] ?? ($cabin->status?->value ?? null);

        // inventory
        if ($cabin->wasChanged('inventory')) {
            $category = $cabin->category;
            $categorySpec = $category->spec;
            GlobalLogger::log(
                LogActionCabin::INVENTORY_CHANGED,
                'cabin',
                $cabin->id,
                sprintf('Inventory %s → %s, category code: %s', $cabin->getRawOriginal('inventory'),
                    $cabin->inventory, $categorySpec->category_code),
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
            $originalNotes = $cabin->getRawOriginal('notes');
            $newNotes = $cabin->notes;

            if (($originalNotes === '' || $originalNotes === null)
                && ($newNotes === '' || $newNotes === null)) {
                return;
            }

            if ($originalNotes === $newNotes) {
                return;
            }

            GlobalLogger::log(
                LogActionCabin::NOTES_UPDATED,
                'cabin',
                $cabin->id,
                'Notes updated',
                [
                    'before' => ['notes' => $originalNotes],
                    'after'  => ['notes' => $newNotes],
                ]
            );
        }

        // internal notes
        if ($cabin->wasChanged('internal_notes')) {
            $originalInternalNotes = $cabin->getRawOriginal('notes');
            $newInternalNotes = $cabin->notes;

            if (($originalInternalNotes === '' || $originalInternalNotes === null)
                && ($newInternalNotes === '' || $newInternalNotes === null)) {
                return;
            }

            if ($originalInternalNotes === $newInternalNotes) {
                return;
            }

            GlobalLogger::log(
                LogActionCabin::INTERNAL_NOTES_UPDATED,
                'cabin',
                $cabin->id,
                'Internal Notes Updated',
                [
                    'before' => ['internal_notes' => $originalInternalNotes],
                    'after'  => ['internal_notes' => $newInternalNotes],
                ]
            );
        }
    }
}
