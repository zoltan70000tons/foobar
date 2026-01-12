<?php

namespace App\Services;

use App\Mail\CabinInventoryIntegrityReport;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ----------------------------------------------
 * Cabin Inventory Integrity Service
 * ----------------------------------------------
 *
 * RULES:
 * 1. cabin.type.unknown - Cabin type is not recognized for integrity checks. ( type probably is always defined, this is more for throw error)
 *
 * 2. private.status.partially_booked - Private cabin status must never be PARTIALLY_BOOKED. ( this is not sinlge ticket)
 * 3. private.available.bookings - Private cabin is AVAILABLE/RESERVED but has active bookings.
 * 4. private.available.inventory - Private cabin is AVAILABLE/RESERVED but inventory is not 1.
 * 5. private.booked.booking_count - Private cabin is BOOKED but booking count is not 1.
 * 6. private.booked.inventory - Private cabin is BOOKED but inventory is not 0.
 * 7. private.booked.passengers - Private cabin is BOOKED but passenger count does not match capacity.
 *
 * 8. single.available.bookings - Single ticket cabin is AVAILABLE/RESERVED but has active bookings.
 * 9. single.available.inventory - Single ticket cabin is AVAILABLE/RESERVED but inventory does not equal capacity.
 * 10. single.booked.booking_count - Single ticket cabin is BOOKED but booking count does not match capacity.
 * 11. single.booked.inventory - Single ticket cabin is BOOKED but inventory is not 0.
 * 12. single.booked.passengers - Single ticket cabin is BOOKED but passenger count does not match capacity.
 * 13. single.partially_booked.status - Single ticket cabin is PARTIALLY_BOOKED but booking count is invalid.
 * 14. single.partially_booked.inventory - Single ticket cabin inventory does not match capacity
 *   minus bookings.
 * 15. single.partially_booked.passengers - Passenger count does not match booking count.
 *
 * 16. general.inventory.negative - Cabin inventory is negative.
 * 17. general.inventory.greater - Cabin inventory is greater than cabin category capacity.
 * 18. general.single.one.entries - For single ticket, passenger entries must match current invoentory logic. 1 pax per ticket sold.
 * 19. general.cabins.closed - Cabins with status CLOSED must not have any bookings linked.
 *
 * ----------------------------------------------
 *
 */

class CabinInventoryIntegrityService {
    private const ACTIVE_EVENT_STATUSES = ['PUBLIC', 'PRE-SALE'];
    // private const IS_CANCELLED_STATUSES = ['CANCELLED'];

    // Run integrity checks for cabins
    public function run(?int $eventId = null): array {
        $events = $this->getActiveEvents($eventId);
        $report = $this->initReport();

        if ($eventId !== null && $events->isEmpty()) {
            $report['message'] = 'No active events matched the requested event id.';
            return $report;
        }

        foreach ($events as $event) {
            $eventResult = $this->checkEvent($event);

            $report['summary']['events_checked']++;
            $report['summary']['cabins_checked'] += $eventResult['cabins_checked'];
            $report['summary']['issues_count'] += $eventResult['issues_count'];
            $report['issues'] = array_merge($report['issues'], $eventResult['issues']);
            $report['events'][] = $eventResult['meta'];
        }

        return $report;
    }

    // Send the report via email
    public function sendReport(array $report): bool {
        $recipients = $this->getRecipients();

        if (empty($recipients)) {
            Log::warning('Cabin inventory integrity report skipped: no recipients configured.');
            return false;
        }

        try {
            Mail::to($recipients)->queue(new CabinInventoryIntegrityReport($report));
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send cabin inventory integrity report: ' . $e->getMessage());
            return false;
        }
    }

    // Event-level logic
    // For each event we get cabins and bookings and check each cabin
    protected function checkEvent(Event $event): array {
        $cabins = $this->getEventCabins($event);
        $bookingsByCabin = $this->getBookingsGroupedByCabin($cabins);

        $issues = [];
        $eventIssues = 0;

        foreach ($cabins as $cabin) {
            $eventIssues += $this->checkCabin($issues, $event, $cabin, $bookingsByCabin->get($cabin->id, collect()));
        }

        return [
            'cabins_checked' => $cabins->count(),
            'issues_count' => $eventIssues,
            'issues' => $issues,
            'meta' => [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_status' => $event->status,
                'cabins_checked' => $cabins->count(),
                'issues_count' => $eventIssues,
            ],
        ];
    }

    // Cabin-level logic
    // It mean that for each cabin we check all the rules and return number of issues found
    protected function checkCabin(array &$issues, Event $event, Cabin $cabin, $bookings): int {
        $capacity = $cabin->category->spec->capacity;

        [$bookingCount, $passengerCount, $bookingIds] = $this->buildBookingContext($bookings);

        $status = $this->normalizeStatus($cabin->status);
        $inventory = (int) ($cabin->inventory ?? 0);

        $this->generalInventoryChecks(
            $issues,
            $event,
            $cabin,
            $status,
            $capacity,
            $inventory,
            $bookingCount,
            $passengerCount,
            $bookingIds,
        );

        if ($this->isSingleCabin($cabin)) {
            return $this->validateSingleCabin(
                $issues,
                $event,
                $cabin,
                $status,
                $capacity,
                $inventory,
                $bookingCount,
                $passengerCount,
                $bookingIds,
            );
        }

        if ($this->isPrivateCabin($cabin)) {
            return $this->validatePrivateCabin(
                $issues,
                $event,
                $cabin,
                $status,
                $capacity,
                $inventory,
                $bookingCount,
                $passengerCount,
                $bookingIds,
            );
        }

        $this->addIssue(
            $issues,
            $event,
            $cabin,
            'cabin.type.unknown',
            'Cabin type is not recognized for integrity checks.',
            [
                'booking_count' => $bookingCount,
                'passenger_count' => $passengerCount,
                'booking_ids' => $bookingIds,
            ],
        );

        return 1;
    }

    // Validate private cabin rules
    protected function validatePrivateCabin(
        array &$issues,
        Event $event,
        Cabin $cabin,
        string $status,
        int $capacity,
        int $inventory,
        int $bookingCount,
        int $passengerCount,
        array $bookingIds,
    ): int {
        $added = 0;

        if ($status === 'PARTIALLY_BOOKED') {
            $this->addIssue(
                $issues,
                $event,
                $cabin,
                'private.status.partially_booked',
                'Private cabin status must never be PARTIALLY_BOOKED.',
                compact('bookingCount', 'passengerCount', 'bookingIds'),
            );
            $added++;
        }

        if (in_array($status, ['AVAILABLE', 'RESERVED'], true)) {
            if ($bookingCount !== 0) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'private.available.bookings',
                    'Private cabin is AVAILABLE/RESERVED but has active bookings.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($inventory !== 1) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'private.available.inventory',
                    'Private cabin is AVAILABLE/RESERVED but inventory is not 1.',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }
        }

        if ($status === 'BOOKED') {
            if ($bookingCount !== 1) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'private.booked.booking_count',
                    'Private cabin is BOOKED but booking count is: ' . $bookingCount . ' instead of 1.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($inventory !== 0) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'private.booked.inventory',
                    'Private cabin is BOOKED but inventory is not 0.',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($passengerCount !== $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'private.booked.passengers',
                    'Private cabin is BOOKED but passenger count does not match capacity.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }
        }

        return $added;
    }

    // Validate single ticket cabin rules
    protected function validateSingleCabin(
        array &$issues,
        Event $event,
        Cabin $cabin,
        string $status,
        int $capacity,
        int $inventory,
        int $bookingCount,
        int $passengerCount,
        array $bookingIds,
    ): int {
        $added = 0;

        // Check AVAILABLE/RESERVED status
        if (in_array($status, ['AVAILABLE', 'RESERVED'], true)) {
            if ($bookingCount !== 0) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.available.bookings',
                    'Single ticket cabin is AVAILABLE/RESERVED but has active bookings.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($inventory !== $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.available.inventory',
                    'Single ticket cabin is AVAILABLE/RESERVED but inventory does not equal capacity.',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }
        }

        // Check BOOKED status
        if ($status === 'BOOKED') {
            if ($bookingCount !== $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.booked.booking_count',
                    'Single ticket cabin is BOOKED but booking count does not match capacity.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($inventory !== 0) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.booked.inventory',
                    'Single ticket cabin is BOOKED but inventory is not 0.',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($passengerCount !== $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.booked.passengers',
                    'Single ticket cabin is BOOKED but passenger count does not match capacity.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            // passenger entries must match current inventory logic. 1 pax per ticket sold.
            if ($passengerCount + $inventory !== $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'general.single.one.entries',
                    'For single ticket cabins, passenger entries must match current inventory logic (1 pax per ticket sold).',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }
        }

        if ($status === 'PARTIALLY_BOOKED') {
            // Inventory must equal (capacity - number of bookings).
            $expectedInventory = $capacity - $bookingCount;

            // Booking count must be greater than 0 and less than capacity.
            if ($bookingCount === 0 || $bookingCount === $capacity) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.partially_booked.status',
                    'Single ticket cabin is PARTIALLY_BOOKED but booking count is invalid.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($inventory !== $expectedInventory) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.partially_booked.inventory',
                    'Single ticket cabin inventory does not match capacity minus bookings. Number of bookings: ' .
                        $bookingCount .
                        ', expected inventory: ' .
                        $expectedInventory .
                        ', actual inventory: ' .
                        $inventory .
                        '.',
                    compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }

            if ($passengerCount !== $bookingCount) {
                $this->addIssue(
                    $issues,
                    $event,
                    $cabin,
                    'single.partially_booked.passengers',
                    'Passenger count does not match booking count.',
                    compact('bookingCount', 'passengerCount', 'bookingIds'),
                );
                $added++;
            }
        }

        return $added;
    }

    // General inventory checks applicable to all cabin types
    protected function generalInventoryChecks(
        array &$issues,
        Event $event,
        Cabin $cabin,
        string $status,
        int $capacity,
        int $inventory,
        int $bookingCount,
        int $passengerCount,
        array $bookingIds,
    ): void {
        // Inventory should not be negative
        if ($inventory < 0) {
            $this->addIssue(
                $issues,
                $event,
                $cabin,
                'general.inventory.negative',
                'Cabin inventory is negative.',
                compact('inventory', 'bookingCount', 'passengerCount', 'bookingIds'),
            );
        }

        // Inventory should not exceed capacity
        if ($inventory > $capacity) {
            $this->addIssue(
                $issues,
                $event,
                $cabin,
                'general.inventory.greater',
                'Cabin inventory is greater than cabin category capacity.',
                compact('inventory', 'capacity', 'bookingCount', 'passengerCount', 'bookingIds'),
            );
        }

        // cabins with status CLOSED must not have any bookings linked
        if ($status === 'CLOSED' && $bookingCount > 0) {
            $this->addIssue(
                $issues,
                $event,
                $cabin,
                'general.cabins.closed',
                'Cabins with status CLOSED must not have any bookings linked.',
                compact('bookingCount', 'passengerCount', 'bookingIds'),
            );
        }
    }

    // -------------- HELPERS --------------
    protected function getActiveEvents(?int $eventId) {
        return Event::query()
            ->whereIn('status', self::ACTIVE_EVENT_STATUSES)
            ->when($eventId, fn($q) => $q->where('id', $eventId))
            ->get();
    }

    // Get cabins for the event with related data
    protected function getEventCabins(Event $event) {
        return Cabin::with(['category.spec', 'cabinSpec', 'cabinType'])
            ->whereHas('category', fn($q) => $q->where('event_id', $event->id))
            ->get();
    }

    // Get bookings grouped by cabin id
    protected function getBookingsGroupedByCabin($cabins) {
        return Booking::withCount('passengers')
            ->whereIn('cabin_id', $cabins->pluck('id'))
            ->where('status', '!=', 'CANCELLED')
            ->get()
            ->groupBy('cabin_id');
    }

    // Build booking context array
    protected function buildBookingContext($bookings): array {
        return [$bookings->count(), $bookings->sum('passengers_count'), $bookings->pluck('id')->all()];
    }

    // Check if cabin is single ticket
    protected function isSingleCabin(Cabin $cabin): bool {
        return in_array($cabin->cabin_type_id, [2, 3], true) ||
            in_array($cabin->cabinType?->cabin_type, ['SINGLE_MALE', 'SINGLE_FEMALE'], true);
    }

    // Check if cabin is private
    protected function isPrivateCabin(Cabin $cabin): bool {
        return $cabin->cabin_type_id === 1;
    }

    // Initialize the report structure
    protected function initReport(): array {
        return [
            'run_at' => now()->toDateTimeString(),
            'summary' => [
                'events_checked' => 0,
                'cabins_checked' => 0,
                'issues_count' => 0,
            ],
            'events' => [],
            'issues' => [],
        ];
    }

    // Add an issue to the report
    protected function addIssue(
        array &$issues,
        Event $event,
        Cabin $cabin,
        string $rule,
        string $message,
        array $context,
    ): void {
        $issues[] = array_merge(
            [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_status' => $event->status,
                'cabin_id' => $cabin->id,
                'cabin_number' => $cabin->cabin_number,
                'cabin_status' => $this->normalizeStatus($cabin->status),
                'cabin_type' => $cabin->cabinType?->cabin_type,
                'capacity_name' => $cabin->category?->capacityDescription,
                'category_code' => $cabin->category?->spec?->category_code,
                'category_capacity' => $cabin->category?->spec?->capacity,
                'inventory' => $cabin->inventory,
                'rule' => $rule,
                'message' => $message,
            ],
            $context,
        );
    }

    // Normalize status to string for comparison
    protected function normalizeStatus(mixed $status): string {
        if ($status instanceof \BackedEnum) {
            return (string) $status->value;
        }

        return (string) $status;
    }

    // Recipients from config / settings for email report
    protected function getRecipients(): array {
        $raw = config('settings.cabin_inventory_integrity_report_recipients', '');
        return array_values(array_unique(array_filter(array_map('trim', preg_split('/[\s,;]+/', (string) $raw)))));
    }
}
