<?php

namespace App\Observers;

use App\Enums\GlobalLog\LogActionCabin;
use App\Models\CabinSpec;
use App\Support\GlobalLogger;

class CabinSpecsObserver
{
    public function updated(CabinSpec $cabinSpec): void
    {
        $cabins = $cabinSpec->cabins;
        $cabinId = $cabins->first()?->id;
        //Used to use the $cabinSpec->id but used as cabin id, but that's incorrect
        //Currently using the first cabin found for the cabin spec, might me also incorrect, but makes more sense

        if ($cabinSpec->wasChanged('cabin_number')) {
            GlobalLogger::log(
                LogActionCabin::CABIN_NUMBER_CHANGED, 'cabin', $cabinId,
                "Cabin number {$cabinSpec->getOriginal('cabin_number')} → {$cabinSpec->cabin_number}",
                ['before' => ['Cabin Number' => $cabinSpec->getOriginal('cabin_number')], 'after' => ['Cabin Number' => $cabinSpec->cabin_number]]
            );
        }

        if ($cabinSpec->wasChanged('deck')) {
            GlobalLogger::log(
                LogActionCabin::DECK_CHANGED, 'cabin', $cabinId,
                "Deck {$cabinSpec->getOriginal('deck')} → {$cabinSpec->deck}",
                ['before' => ['Deck' => $cabinSpec->getOriginal('deck')], 'after' => ['Deck' => $cabinSpec->deck]]
            );
        }

        if ($cabinSpec->wasChanged('total_berths')) {
            GlobalLogger::log(
                LogActionCabin::TOTAL_BERTHS_CHANGED, 'cabin', $cabinId,
                "Total berths {$cabinSpec->getOriginal('total_berths')} → {$cabinSpec->total_berths}",
                ['before' => ['Total berths' => $cabinSpec->getOriginal('total_berths')], 'after' => ['Total berths' => $cabinSpec->total_berths]]
            );
        }

        if ($cabinSpec->wasChanged('upper_berths')) {
            GlobalLogger::log(
                LogActionCabin::UPPER_BERTHS_CHANGED, 'cabin', $cabinId,
                "Upper berths {$cabinSpec->getOriginal('upper_berths')} → {$cabinSpec->upper_berths}",
                ['before' => ['Upper berths' => $cabinSpec->getOriginal('upper_berths')], 'after' => ['Upper berths' =>
                    $cabinSpec->upper_berths]]
            );
        }

        if ($cabinSpec->wasChanged('lower_bed_type_1')) {
            GlobalLogger::log(
                LogActionCabin::LOWER_BED_TYPE_1_CHANGED, 'cabin', $cabinId,
                "Lower bed type 1 {$cabinSpec->getOriginal('lower_bed_type_1')} → {$cabinSpec->lower_bed_type_1}",
                ['before' => ['Lower bed type 1' => $cabinSpec->getOriginal('lower_bed_type_1')], 'after' => ['Lower bed type 1'
                =>
                    $cabinSpec->lower_bed_type_1]]
            );
        }

        if ($cabinSpec->wasChanged('lower_bed_type_2')) {
            GlobalLogger::log(
                LogActionCabin::LOWER_BED_TYPE_2_CHANGED, 'cabin', $cabinId,
                "Lower bed type 2 {$cabinSpec->getOriginal('lower_bed_type_2')} → {$cabinSpec->lower_bed_type_2}",
                ['before' => ['Lower bed type 2' => $cabinSpec->getOriginal('lower_bed_type_2')], 'after' => ['Lower bed type 2'
                =>
                    $cabinSpec->lower_bed_type_2]]
            );
        }

        if ($cabinSpec->wasChanged('accessible')) {
            $original = $cabinSpec->getOriginal('accessible') === false ? "False" : "True";
            $new = $cabinSpec->accessible === true ? "True" : "False";
            GlobalLogger::log(
                LogActionCabin::ACCESSIBILITY_CHANGED, 'cabin', $cabinId,
                "Accessible {$original} → {$new}",
                ['before' => ['Accessible' => $cabinSpec->getOriginal('accessible')], 'after' => ['Accessible' =>
                    $cabinSpec->accessible]]
            );
        }

        if ($cabinSpec->wasChanged('obstructed_view')) {
            $original = $cabinSpec->getOriginal('obstructed_view') === false ? "False" : "True";
            $new = $cabinSpec->obstructed_view === true ? "True" : "False";
            GlobalLogger::log(
                LogActionCabin::OBSTRUCTED_VIEW_CHANGED, 'cabin', $cabinId,
                "Obstructed View {$original} → {$new}",
                ['before' => ['Obstructed View' => $cabinSpec->getOriginal('obstructed_view')], 'after' => ['Obstructed View' =>
                    $cabinSpec->obstructed_view]]
            );
        }

        if ($cabinSpec->wasChanged('connects_with')) {
            GlobalLogger::log(
                LogActionCabin::CONNECTS_WITH_CHANGED, 'cabin', $cabinId,
                "Connects with {$cabinSpec->getOriginal('connects_with')} → {$cabinSpec->connects_with}",
                ['before' => ['Connects with' => $cabinSpec->getOriginal('connects_with')], 'after' => ['Connects with' =>
                    $cabinSpec->connects_with]]
            );
        }

        if ($cabinSpec->wasChanged('location')) {
            GlobalLogger::log(
                LogActionCabin::LOCATION_CHANGED, 'cabin', $cabinId,
                "Location {$cabinSpec->getOriginal('location')} → {$cabinSpec->location}",
                ['before' => ['Location' => $cabinSpec->getOriginal('location')], 'after' => ['Location' =>
                    $cabinSpec->location]]
            );
        }

        if ($cabinSpec->wasChanged('balcony')) {
            $original = $cabinSpec->getOriginal('balcony') === false ? "False" : "True";
            $new = $cabinSpec->balcony === true ? "True" : "False";
            GlobalLogger::log(
                LogActionCabin::BALCONY_CHANGED, 'cabin', $cabinId,
                "Balcony {$original} → {$new}",
                ['before' => ['Balcony' => $cabinSpec->getOriginal('balcony')], 'after' => ['Balcony' =>
                    $cabinSpec->balcony]]
            );
        }
    }
}
