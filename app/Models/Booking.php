<?php

namespace App\Models;

use App\Enums\GlobalLog\LogActionBooking;
use App\Support\GlobalLogger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\BookingLog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Hidehalo\Nanoid\Client;
use Illuminate\Support\Facades\DB;
use Log;
use Storage;
use App\Models\Tag;
use Str;
use App\Models\Log as LogModel;

class Booking extends Model
{
  use HasFactory;

  protected $fillable = [
    'booking_code',
    'booking_request_id',
    'event_id',
    'customer_id',
    'payment_plan',
    'cabin_id',
    'is_single_occupancy',
    'bed_config',
    'agent_id',
    'status',
  ];

  public function getMorphableIdAttribute(): string
  {
    return (string) $this->id;
  }

  /**
   * Relationship: A booking belongs to many adjustments.
   */
  public function adjustments(): BelongsToMany
  {
    return $this->belongsToMany(Adjustment::class, 'booking_has_adjustments');
  }

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function customer()
  {
    return $this->belongsTo(User::class, 'customer_id');
  }

  /**
   * Relationship: A booking have one event id.
   */
  public function event()
  {
    return $this->belongsTo(Event::class, 'event_id');
  }

  /**
   * Relationship: A booking belongs to a customer.
   */
  public function agent()
  {
    return $this->belongsTo(User::class, 'agent_id');
  }

  /**
   * Relationship: A booking belongs to one cabin (one-to-one).
   */
  public function cabin()
  {
    return $this->belongsTo(Cabin::class, 'cabin_id');
  }

  public function passengers()
  {
    return $this->hasMany(Passenger::class, 'booking_id')->orderBy('passenger_order');
  }

  /**
   * Assign a cabin to the booking and update the cabin's inventory and status.
   *
   * @param  \App\Models\Cabin  $cabin
   * @return void
   * @throws \Exception if the cabin is already fully booked or has no available inventory
   */

  public function assignCabin(Cabin $cabin): array|bool
  {
    try {
      // Validate that the cabin has available inventory
      if ($cabin->inventory <= 0) {
        throw new \Exception('This cabin has no available inventory.');
      }

      // Check if the cabin is already fully booked
      if ($cabin->status === 'BOOKED') {
        throw new \Exception('This cabin is already fully booked.');
      }

      // Assign the cabin to this booking
      $this->cabin_id = $cabin->id;

      // Save the updated booking
      $this->save();

      // Update the cabin's inventory and status
      $cabin->updateInventoryOnBooking();

      // Return true if successful
      return true;
    } catch (\Exception $e) {
      // Return an error array in case of exception
      return [
        'error' => true,
        'message' => $e->getMessage(),
      ];
    }
  }

  public function logsSafe()
  {
    return LogModel::with('actor')
      ->where('related_type', 'booking')
      ->where('related_id', $this->morphable_id);
  }

  public function comments()
  {
    return $this->hasMany(Comment::class, 'booking_id', 'id');
  }

  public function lockedBy()
  {
    return $this->hasOne(BookingAgentSessions::class, 'booking_id', 'id');
  }

  public function changeCabin(Cabin $cabin)
  {
    try {
      // Validate that the cabin exists
      if (!$cabin) {
        $number = $cabin->cabinSpec->cabin_number ?? null;
        $message = "Cabin " . ($number === null ? "" : "with number {$number} ") . "does not exist.";
        throw new \Exception($message);
      }

      // Check if the cabin is available
      if ($cabin->inventory <= 0) {
        throw new \Exception('This cabin has no available inventory.');
      }

      if ($cabin->status === 'BOOKED') {
        throw new \Exception('This cabin is already fully booked.');
      }

      // Get the previous cabin
      $prevCabin = $this->cabin;

      // Check if the cabin is of the same type as the current booking
      if ($cabin->cabinType->id !== $prevCabin->cabinType->id) {
        throw new \Exception('This cabin is not of the same type as the current booking.');
      }

      // Release the previous cabin
      $prevCabin->releaseCabin();

      // Assign the new cabin to the booking
      $this->assignCabin($cabin);

      // Generate a new booking code
      $preBookingCode = $this->booking_code;
      $this->booking_code = $this->generateBookingCode($cabin);
      $this->save();

      $oldCabin = $prevCabin;
      $oldCabinCategory = $oldCabin->category;
      $oldCabinCategorySpec = $oldCabinCategory->spec;
      $newCabin = $cabin;
      $newCabinCategory = $newCabin->category;
      $newCabinCategorySpec = $newCabinCategory->spec;

      GlobalLogger::log(
        LogActionBooking::CABIN_NUMBER_CHANGED,
        'booking',
        $this->id,
        'Changed Cabin',
        [
          'before' => [
            'cabinNumber' => $prevCabin->cabin_number,
            'bookingCode' => $preBookingCode,
            'categoryCode' => $oldCabinCategorySpec->category_code,
          ],
          'after' => [
            'cabinNumber' => $cabin->cabin_number,
            'bookingCode' => $this->booking_code,
            'categoryCode' => $newCabinCategorySpec->category_code,
          ],
        ],
      );

      return $this;
    } catch (\Exception $e) {
      return ['error' => $e->getMessage()];
    }
  }


  /**
   * Check if a given 4-character code is in the blocked words list.
   *
   * This function loads the blocked words from a JSON file located in 
   * resources/data/not_allowed_words.json. If the file doesn't exist 
   * or is malformed, it logs an error and throws an exception.
   *
   * @param  string  $code  The 4-character code to validate.
   * @return bool  True if the code is blocked, false otherwise.
   *
   * @throws \Exception If the blocked words file is missing or invalid.
   */
  protected function containsBlockedWords($code)
  {
    static $blocked_words = null;

    if (is_null($blocked_words)) {
      $path = resource_path('data/not_allowed_words.json');

      if (!file_exists($path)) {
        Log::error("Blocked words file not found: $path");
        throw new \Exception("Blocked words file is missing.");
      }

      $json = file_get_contents($path);
      $decoded = json_decode($json, true);

      if (empty($decoded) || !is_array($decoded)) {
        Log::error("Blocked words file is empty or invalid: $path");
        throw new \Exception("Blocked words file is empty or malformed.");
      }

      $blocked_words = array_map('strtoupper', $decoded);
    }

    return in_array(strtoupper($code), $blocked_words);
  }


  public function cancel()
  {
    try {
      Log::info('inside try ok');
      if ($this->status === 'CANCELLED') {
        throw new \Exception('This booking is already cancelled.');
      }
      $this->status = 'CANCELLED';

      $this->cabin->releaseCabin();

      $this->lockedBy()->delete();

      $this->save();

      return true;
    } catch (\Exception $e) {
      Log::info('Error cancelling booking: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Generate a unique booking code for this booking.
   *
   * Format: [Cabin Number][Random Code]-[Year][Product][Category Number][Category Letter]
   * Example: 2314TZHF-F11A
   *
   * Where:
   * - Cabin Number: 4-digit cabin number (e.g., "2314").
   * - Random Code: 4-character random code (e.g., "TZHF").
   * - Year: Fixed character representing the year (e.g., "F").
   * - Product: Fixed character representing the product (e.g., "1").
   * - Category Number: Fixed character representing the category number (e.g., "1").
   * - Category Letter: Letter based on the cabin's category and display order (e.g., "A").
   *
   * @param  \App\Models\Cabin  $cabin
   * @return string  The generated booking code.
   */
  private function generateBookingCode(Cabin $cabin): string
  {
    $characters = config('whitelist.allowed_characters');
    $year = 'F';

    // Define the alphabet, excluding letters I, S, and O because they can be confused with numbers
    $alphabet = array_values(array_diff(range('A', 'Z'), ['I', 'S', 'O']));

    // Get the category number of the cabin
    $categoryNumber = $cabin->category->category_number;

    // Retrieve all categories with the same category number, ordered by display order
    $categoriesInSameGroup = CabinCategory::whereHas('spec', function ($query) use ($categoryNumber) {
      $query->where('category_number', $categoryNumber);
    })
      ->with('spec')
      ->get()
      ->sortBy(fn($cat) => $cat->spec->display_order)
      ->values();

    // Find the index of the current category in that group
    $index = $categoriesInSameGroup->search(function ($cat) use ($cabin) {
      return $cat->id === $cabin->category->id;
    });

    // Find the relative position of the cabin's category in the group
    $index = $categoriesInSameGroup
      ->pluck('id')
      ->search($cabin->category->id);

    // Determine the category letter based on the index
    $categoryLetter = $alphabet[$index % count($alphabet)] ?? '?';

    // Generate a random 4-character code, ensuring it does not contain blocked words
    do {
      $identifier_code = substr(str_shuffle($characters), 0, 4);
    } while ($this->containsBlockedWords($identifier_code));

    // Construct and return the booking code
    return "{$cabin->cabin_number}{$identifier_code}-{$year}1{$categoryNumber}{$categoryLetter}";
  }


  protected static function boot()
  {
    parent::boot();

    static::creating(function ($booking) {
      if (!$booking->booking_code && $booking->cabin) {
        $booking->booking_code = $booking->generateBookingCode($booking->cabin);
      }

      // @JG BOOKING REQUEST CODE
      if (!$booking->booking_request_id) {
        $client = new Client();
        $booking->booking_request_id = $client->formattedId('0123456789ABCDEFGHIJKLMNOPERSTUWXYZ', 10);
      }
      // Set status to 'NEW' if not already set
      if (!$booking->status) {
        $booking->status = 'NEW';
      }
    });

    static::created(function (Booking $booking) {
      if (app()->runningInConsole()) return;

      $tag = Tag::firstOrCreate(
        ['type' => 'booking', 'name' => 'NEW'],
        ['color' => '#ff9800']
      );

      $id = (string) $tag->getKey();
      if (!\Illuminate\Support\Str::isUuid($id)) {
        Log::warning('invalid tag key', ['id' => $id, 'attrs' => $tag->getAttributes()]);
        return;
      }
      $booking->tags()->sync($tag);
      // $booking->tags()->syncWithoutDetaching([$id]);

      if ($booking->relationLoaded('cabin') ? $booking->cabin : $booking->cabin()->exists()) {
        $booking->cabin->updateInventoryOnBooking();
      }
    });




    static::deleted(function ($booking) {});
  }

  public function getTotalpaid()
  {
    return $this->passengers->sum('passenger_balance');
  }

  public function getGrandTotal()
  {
    return $this->passengers->sum('passenger_allocated_cost');
  }

  public function getTaggingKeyAttribute(): string
  {
    return (string) $this->getAttribute($this->getKeyName());
  }

  public function tags()
  {
    return $this->morphToMany(
      Tag::class,
      'entity',
      table: 'taggings',
      foreignPivotKey: 'entity_id',
      relatedPivotKey: 'tag_id',
      parentKey: 'tagging_key',
      relatedKey: 'id'
    )->withPivot('created_at');
  }


  public function attachTags(array $tagIds): void
  {
    $this->tags()->syncWithoutDetaching($tagIds);
  }

  public function syncTags(array $tagIds): void
  {
    $this->tags()->sync($tagIds);
  }

  public function detachTag(string $tagId): void
  {
    $this->tags()->detach($tagId);
  }
}
