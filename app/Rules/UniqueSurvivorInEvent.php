<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Booking;

class UniqueSurvivorInEvent implements ValidationRule
{
    protected $eventId;
    protected $bookingId;

    public function __construct($eventId, $bookingId = null)
    {
        $this->eventId = $eventId;
        $this->bookingId = $bookingId;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Booking::whereHas('passengers', function ($query) use ($value) {
            $query->where('survivor_number', $value);
        })
        ->where('event_id', $this->eventId)
        ->when($this->bookingId, function ($query) {
            $query->where('id', '!=', $this->bookingId);
        })
        ->exists();

        if ($exists) {
            $fail('Passenger already exists in another booking for the event.');
        }
    }
}