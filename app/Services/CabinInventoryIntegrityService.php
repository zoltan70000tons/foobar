<?php

namespace App\Services;

use App\Mail\CabinInventoryIntegrityReport;
use App\Models\Booking;
use App\Models\Cabin;
use App\Models\Event;
use App\Services\CabinInventoryIntegrity\CabinRuleRegistry;
use App\Services\CabinInventoryIntegrity\Context\CabinIntegrityContext;
use App\Services\CabinInventoryIntegrity\Context\GroupIntegrityContext;
use App\Services\CabinInventoryIntegrity\Rules\RuleResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ----------------------------------------------
 * Cabin Inventory Integrity Service
 * ----------------------------------------------
 *
 * This service checks the integrity of cabin inventory data for events.
 * It applies a set of predefined rules to identify inconsistencies
 * ----------------------------------------------
 *
 */
class CabinInventoryIntegrityService {
    private const ACTIVE_EVENT_STATUSES = ['PUBLIC', 'PRE-SALE'];
    // private const IS_CANCELLED_STATUSES = ['CANCELLED'];

    private CabinRuleRegistry $rules;

    public function __construct(CabinRuleRegistry $rules) {
        $this->rules = $rules;
    }

    // Run integrity checks for all active events or a specific event
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

    // Send integrity report via email
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

    // Check integrity for a single event
    protected function checkEvent(Event $event): array {
        $cabins = $this->getEventCabins($event);
        $bookingsByCabin = $this->getBookingsGroupedByCabin($cabins);

        $issues = [];
        $eventIssues = 0;
        $cabinsChecked = 0;

        $cabinsGrouped = $cabins->groupBy(fn($cabin) => $cabin->cabin_spec_id . ':' . $cabin->event_id);

        foreach ($cabinsGrouped as $group) {
            $cabin = $group->first();
            $groupBookings = $group->flatMap(fn($item) => $bookingsByCabin->get($item->id, collect()))->values();

            $eventIssues += $this->applyGroupRules($issues, $event, $group, $bookingsByCabin);
            $eventIssues += $this->applyCabinRules($issues, $event, $cabin, $groupBookings);
            $cabinsChecked++;
        }

        return [
            'cabins_checked' => $cabinsChecked,
            'issues_count' => $eventIssues,
            'issues' => $issues,
            'meta' => [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_status' => $event->status,
                'cabins_checked' => $cabinsChecked,
                'issues_count' => $eventIssues,
            ],
        ];
    }

    // Apply cabin-level integrity checks
    protected function applyCabinRules(array &$issues, Event $event, Cabin $cabin, $bookings): int {
        $context = $this->buildCabinContext($event, $cabin, $bookings);
        $added = 0;

        // General rules
        foreach ($this->rules->general() as $rule) {
            $added += $this->applyRuleResults($issues, $event, $cabin, $rule->check($context));
        }

        if ($context->isSingleCabin) {
            foreach ($this->rules->single() as $rule) {
                $added += $this->applyRuleResults($issues, $event, $cabin, $rule->check($context));
            }

            return $added;
        }

        if ($context->isPrivateCabin) {
            foreach ($this->rules->private() as $rule) {
                $added += $this->applyRuleResults($issues, $event, $cabin, $rule->check($context));
            }

            return $added;
        }

        return $added +
            $this->applyRuleResults($issues, $event, $cabin, $this->rules->unknownCabinType()->check($context));
    }

    // Apply group-level integrity checks
    protected function applyGroupRules(array &$issues, Event $event, $group, $bookingsByCabin): int {
        $context = $this->buildGroupContext($event, $group, $bookingsByCabin);
        $added = 0;

        foreach ($this->rules->group() as $rule) {
            $added += $this->applyRuleResults($issues, $event, $context->referenceCabin, $rule->check($context));
        }

        return $added;
    }

    // Apply rule results to issues list
    protected function applyRuleResults(array &$issues, Event $event, Cabin $cabin, array $results): int {
        $added = 0;

        foreach ($results as $result) {
            $this->addIssue($issues, $event, $cabin, $result->rule, $result->message, $result->context);
            $added++;
        }

        return $added;
    }

    // Build cabin context for rules
    protected function buildCabinContext(Event $event, Cabin $cabin, $bookings): CabinIntegrityContext {
        $capacity = $cabin->category->spec->capacity;
        [$bookingCount, $passengerCount, $bookingIds, $bookingStatuses] = $this->buildBookingContext($bookings);

        return new CabinIntegrityContext(
            event: $event,
            cabin: $cabin,
            capacity: $capacity,
            status: $this->normalizeStatus($cabin->status),
            inventory: (int) ($cabin->inventory ?? 0),
            bookingCount: $bookingCount,
            passengerCount: $passengerCount,
            bookingIds: $bookingIds,
            bookingStatuses: $bookingStatuses,
            isSingleCabin: $this->isSingleCabin($cabin),
            isPrivateCabin: $this->isPrivateCabin($cabin),
        );
    }

    // Build group context for rules
    protected function buildGroupContext(Event $event, $group, $bookingsByCabin): GroupIntegrityContext {
        $cabins = $group->values();
        $referenceCabin = $cabins->first();

        $statusByCabin = $cabins
            ->mapWithKeys(function ($cabin) {
                return [$cabin->id => $this->normalizeStatus($cabin->status)];
            })
            ->all();

        $inventoryByCabin = $cabins
            ->mapWithKeys(function ($cabin) {
                return [$cabin->id => (int) ($cabin->inventory ?? 0)];
            })
            ->all();

        $bookingCountsByCabin = $cabins
            ->mapWithKeys(function ($cabin) use ($bookingsByCabin) {
                return [$cabin->id => $bookingsByCabin->get($cabin->id, collect())->count()];
            })
            ->all();

        return new GroupIntegrityContext(
            event: $event,
            cabins: $cabins,
            referenceCabin: $referenceCabin,
            referenceStatus: $statusByCabin[$referenceCabin->id] ?? '',
            referenceInventory: $inventoryByCabin[$referenceCabin->id] ?? 0,
            statusByCabin: $statusByCabin,
            inventoryByCabin: $inventoryByCabin,
            bookingCountsByCabin: $bookingCountsByCabin,
        );
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
        $bookingStatuses = $bookings
            ->pluck('status')
            ->map(function ($status) {
                $normalized = trim($this->normalizeStatus($status));
                return $normalized === '' ? null : strtoupper($normalized);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            $bookings->count(),
            $bookings->sum('passengers_count'),
            $bookings->pluck('id')->all(),
            $bookingStatuses,
        ];
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
