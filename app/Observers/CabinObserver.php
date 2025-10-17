<?php

// app/Observers/CabinObserver.php
namespace App\Observers;

use App\Enums\GlobalLog\LogActionCabin;
use App\Models\Cabin;
use App\Support\GlobalLogger;

class CabinObserver
{
    public function updated(Cabin $cabin): void
    {
        // inventory
        if ($cabin->wasChanged('inventory')) {

            GlobalLogger::log(
                LogActionCabin::INVENTORY_CHANGED, 'cabin', $cabin->id,
                "Inventory {$cabin->getOriginal('inventory')} → {$cabin->inventory}",
                ['before'=>['inventory'=>$cabin->getOriginal('inventory')],'after'=>['inventory'=>$cabin->inventory]]
            );
        }

        // status
        if ($cabin->wasChanged('status')) {
            GlobalLogger::log(
                LogActionCabin::STATUS_CHANGED, 'cabin', $cabin->id,
                "{$cabin->getOriginal('status')} → {$cabin->status}",
                ['before'=>['status'=>$cabin->getOriginal('status')],'after'=>['status'=>$cabin->status]]
            );
        }

        // category
        if ($cabin->wasChanged('cabin_category_id')) {
            GlobalLogger::log(
                LogActionCabin::CATEGORY_CHANGED, 'cabin', $cabin->id,
                "Category {$cabin->getOriginal('cabin_category_id')} → {$cabin->cabin_category_id}",
                ['before'=>['category_id'=>$cabin->getOriginal('cabin_category_id')],'after'=>['cabin_category_id'=>$cabin->cabin_category_id]]
            );
        }

        // type
        if ($cabin->wasChanged('cabin_type_id')) {
            GlobalLogger::log(
                LogActionCabin::TYPE_CHANGED, 'cabin', $cabin->id,
                "TYPE {$cabin->getOriginal('cabin_type_id')} → {$cabin->cabin_type_id}",
                ['before'=>['cabin_type_id'=>$cabin->getOriginal('cabin_type_id')],'after'=>['cabin_type_id'=>$cabin->cabin_type_id]]
            );
        }

        //notes
        if ($cabin->wasChanged('notes')) {
            GlobalLogger::log(
                LogActionCabin::NOTES_UPDATED, 'cabin', $cabin->id,
                "Notes updated",
                ['before'=>['Notes'=>$cabin->getOriginal('notes')],'after'=>['notes'=>$cabin->notes]]
            );
        }

        //internal notes
        if ($cabin->wasChanged('internal_notes')) {
            GlobalLogger::log(
                LogActionCabin::INTERNAL_NOTES_UPDATED, 'cabin', $cabin->id,
                "Internal Notes Updated",
                ['before'=>['internal_notes'=>$cabin->getOriginal('internal_notes')],'after'=>['interal_notes'=>$cabin->internal_notes]]
            );
        }

    }
}
