<?php

namespace App\Models;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Log;
use Str;

class Passenger extends Model
{
  use HasApiTokens, HasFactory;

  protected $table = 'passengers';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'booking_id',
    'confirmed_booking_email',
    'lead_passenger',
    'survivor_number',
    'payment_method',
    'gender',
    'first_name',
    'middle_name',
    'last_name',
    'dob',
    'citizenship',
    'address_first',
    'address_second',
    'city',
    'state',
    'postal_code',
    'country',
    'email',
    'phone',
    'emergency_c_name',
    'emergency_c_phone',
    'special_request',
    'special_options',
    'hear_about',
    // 'referral_details',
    'newsletter',
    'travel_info',
    'terms_n_cons',
    'empty_seat',
    'cabin_conf_accp',
    'single_t_agreement',
    'passenger_allocated_cost',
    'passenger_order',
    'passenger_balance',
    'was_on_board',
    'language',
  ];

  protected $appends = ['full_name', 'empty', 'paymentInfo'];

  protected $casts = [
    'special_options' => 'array',
  ];

  /**
   * Mutator: Set Personal Details to Uppercase.
   */
  public function setFirstNameAttribute($value)
  {
    $this->attributes['first_name'] = strtoupper($value);
  }

  public function setLastNameAttribute($value)
  {
    $this->attributes['last_name'] = strtoupper($value);
  }

  public function setMiddleNameAttribute($value)
  {
    $this->attributes['middle_name'] = strtoupper($value);
  }

  /**
   * Relationship: A passenger belongs to a booking.
   */
  public function booking()
  {
    return $this->belongsTo(Booking::class);
  }

  public function passengerInvitation()
  {
    return $this->hasMany(PassengerInvitation::class, 'passenger_id');
  }

  public function getFullNameAttribute()
  {
    return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
  }

  public function getEmptyAttribute()
  {
    return $this->empty_seat;
  }

  // passenger may have installments
  public function installments()
  {
    return $this->hasMany(Installment::class);
  }

  public function payments()
  {
    return $this->hasMany(Payment::class);
  }

  public function fees()
  {
    return $this->hasMany(Fee::class);
  }

  public function discounts()
  {
    return $this->hasMany(PassengerDiscount::class);
  }
  public function getPaymentInfoAttribute()
  {
    try {
      $passenger = $this;
      $totalCost = $passenger->passenger_allocated_cost;

      $passenger = $this;
      $booking = $passenger->booking;
      $totalCost = $passenger->passenger_allocated_cost;
      $totalPaid = $this->getTotalPayment();

      $installments = Installment::where('passenger_id', $this->id)
        ->with(['payments', 'fee'])
        ->orderBy('due_date')
        ->get();
      $feePayments = $installments->filter(fn($i) => optional($i)->type === 'FEE');
      $totalFeeAmount = $feePayments->sum(fn($fee) => optional($fee->fee)->amount ?? 0);
      if ($booking->payment_plan === 'PAY_IN_FULL') {
        $installmentsList = collect();

        $installmentsList[] = [
          'id' => null,
          'passenger_id' => $passenger->id,
          'amount' => round($totalCost - $totalFeeAmount, 2),
          'total_paid' => round($totalPaid, 2),
          'remaining_amount' => round(max(0, $totalCost - $totalPaid), 2),
          'status' => $totalPaid >= $totalCost - $totalFeeAmount ? 'PAID' : 'PENDING',
          'due_date' => null,
          'type' => 'PAYMENT',
          'payment_date' => null,
          'code' => 'PAY_IN_FULL',
        ];

        foreach ($feePayments as $fee) {
          $installmentsList[] = [
            'id' => $fee->id,
            'passenger_id' => $fee->passenger_id,
            'amount' => round(optional($fee->fee)->amount ?? 0, 2),
            'total_paid' => 0,
            'remaining_amount' => round(optional($fee->fee)->amount ?? 0, 2),
            'status' => 'PENDING',
            'due_date' => null,
            'type' => 'FEE',
            'payment_date' => null,
            'code' => optional($fee->fee)->type ?? 'N/A',
          ];
        }
        return [
          'success' => true,
          'installments' => $installmentsList,
          'allocated_cost' => round($totalCost, 2),
          'installments_number' => count($installmentsList),
          'total_fees' => round($feePayments->sum(fn($fee) => optional($fee->fee)->amount ?? 0), 2),
          'is_fully_paid' => $totalPaid >= $totalCost,
          'total_paid' => round($totalPaid, 2),
          'outstanding_balance' => round($totalCost - $totalPaid, 2),
        ];
      }
      $installments = Installment::where('passenger_id', $this->id)
        ->with(['payments', 'fee'])
        ->orderBy('due_date')
        ->get();

      if ($installments->isEmpty()) {
        return ['success' => false, 'message' => 'No installments found for this passenger'];
      }

      $installmentPayments = $installments->filter(fn($i) => optional($i)->type === 'PAYMENT');
      $feePayments = $installments->filter(fn($i) => optional($i)->type === 'FEE');
      $totalInstallments = $installmentPayments->count();
      $totalFee = $this->getTotalFees();

      if ($totalInstallments === 0) {
        return ['success' => false, 'message' => 'No installments found for this passenger'];
      }

      $paymentAmount = ($totalCost - $totalFee) / max(1, $totalInstallments);

      $mergedDetails = $installments->map(function ($installment) use ($paymentAmount) {
        $isFee = $installment->type === 'FEE';
        $installmentAmount = $isFee ? optional($installment->fee)->amount ?? 0 : $paymentAmount;
        $lastPaymentDate = optional($installment->payments->sortByDesc('transaction_date')->first())->transaction_date;
        $code = $isFee && isset($installment->fee->type) ? Str::title($installment->fee->type) : null;
        return [
          'id' => $installment->id,
          'passenger_id' => $installment->passenger_id,
          'amount' => round($installmentAmount, 2),
          'total_paid' => 0,
          'remaining_amount' => round($installmentAmount, 2),
          'status' => 'PENDING',
          'due_date' => $installment->due_date,
          'type' => $installment->type,
          'payment_date' => $lastPaymentDate,
          'code' => $code,
        ];
      });

      $totalPaid = $this->getTotalPayment();
      $remainingPayment = $totalPaid;

      $mergedDetails = $mergedDetails->map(function ($installment) use (&$remainingPayment) {
        if ($remainingPayment > 0) {
          $paidToInstallment = min($remainingPayment, $installment['amount']);
          $remainingPayment -= $paidToInstallment;

          $installment['total_paid'] = round($paidToInstallment, 2);
          $installment['remaining_amount'] = round(max(0, $installment['amount'] - $paidToInstallment), 2);

          if ($installment['total_paid'] >= $installment['amount']) {
            $installment['status'] = 'PAID';
          } elseif ($installment['total_paid'] > 0) {
            $installment['status'] = 'PARTIALLY_PAID';
          }

          if ($installment['status'] !== 'PAID' && $installment['due_date'] < now()) {
            $installment['status'] = 'OVERDUE';
          }
        }

        return $installment;
      });

      return [
        'success' => true,
        'installments' => $mergedDetails,
        'allocated_cost' => round($totalCost, 2),
        'installments_number' => $totalInstallments,
        'total_fees' => round($totalFee, 2),
        'is_fully_paid' => $totalPaid >= $totalCost,
        'total_paid' => round($totalPaid, 2),
        'outstanding_balance' => round($totalCost - $totalPaid, 2),
      ];
    } catch (Exception $e) {
      Log::error('Error fetching installments: ' . $e->getMessage());
      return ['success' => false, 'message' => 'Failed to fetch installments'];
    }
  }

  /**
   * Format Installments
   */
  private function formatInstallment($installment, $installmentAmount)
  {
    $totalPaid = $installment->payments->sum('pivot.amount_paid');
    $currentDate = now();
    $status = 'PENDING';

    if ($totalPaid >= $installmentAmount) {
      $status = 'PAID';
    } elseif ($totalPaid > 0) {
      $status = 'PARTIALLY_PAID';
    }

    if ($status !== 'PAID' && $installment->due_date < $currentDate) {
      $status = 'OVERDUE';
    }

    return [
      'id' => $installment->id,
      'passenger_id' => $installment->passenger_id,
      'amount' => round($installmentAmount, 2),
      'total_paid' => round($totalPaid, 2),
      'remaining_amount' => round(max(0, $installmentAmount - $totalPaid), 2),
      'status' => $status,
      'due_date' => $installment->due_date,
      'type' => optional($installment)->type,
    ];
  }

  public function getAllPayments()
  {
    return DB::table('payments as p')
      ->distinct()
      ->join('installment_payment as ip', 'p.id', '=', 'ip.payment_id')
      ->where('p.passenger_id', $this->id)
      ->select('p.*')
      ->get();
  }

  public function getTotalPayment()
  {
    return DB::table('payments as p')
      ->where('p.passenger_id', $this->id)
      ->whereExists(function ($query) {
        $query->select(DB::raw(1))->from('installment_payment as ip')->whereColumn('ip.payment_id', 'p.id');
      })
      ->sum('p.amount');
  }

  public function getTotalFees()
  {
    return $this->fees()->sum('amount');
  }

  public function getNextInstallmentAttribute()
  {
    $info = $this->getPaymentInfoAttribute();
    if (!isset($info['installments']) || empty($info['installments'])) {
      return null;
    }
    $nextInstallment = collect($info['installments'])
      ->where('status', 'PENDING')
      ->sortBy('due_date')
      ->first();
    if (!$nextInstallment) {
      return [
        'due_date' => now()->toDateString(),
        'amount' => round(0, 2),
      ];
    }
    return [
      'due_date' => $nextInstallment['due_date'],
      'amount' => round($nextInstallment['remaining_amount'], 2),
    ];
  }

  /**
   * This method checks if a survivor number is already associated with an active booking in the same event.
   */
  public static function checkSurvivorInActiveBookings(string $survivorNumber, int $eventId): bool
  {
    return self::where('survivor_number', $survivorNumber)
      ->whereHas('booking', function ($query) use ($eventId) {
        $query->where('status', '!=', 'CANCELLED')
          ->where('event_id', $eventId);
      })
      ->exists();
  }

  /**
   * Get the installment status for a passenger.
   */
  public function getInstallmentStatus(): array
  {
    $balance = $this->passenger_balance ?? 0;
    $today = now()->startOfDay();

    // Fetch all installments for the passenger (FEE and PAYMENT)
    $installments = $this->installments()
      ->with('fee')
      ->orderBy('due_date')
      ->get();

    // Calculate total fees and how much each payment installment should be
    $feeAmountTotal = $this->fees()->sum('amount');
    $paymentInstallments = $installments->where('type', 'PAYMENT');
    $paymentCount = $paymentInstallments->count();

    $installmentAmount = $paymentCount > 0
      ? ($this->passenger_allocated_cost - $feeAmountTotal) / $paymentCount
      : 0;

    $paidInstallments = [];
    $remainingInstallments = [];
    $nextInstallment = null;

    // Loop through all installments, oldest first
    foreach ($installments as $installment) {
      $isDue = Carbon::parse($installment->due_date)->lte($today);

      // Determine the expected amount for this installment
      $amount = $installment->type === 'FEE'
        ? ($installment->fee->amount ?? 0)
        : $installmentAmount;

      if ($isDue && $balance >= $amount) {
        // Installment is due and has been covered by balance
        $balance -= $amount;
        $paidInstallments[] = [
          'installment_id' => $installment->id,
          'type' => $installment->type,
          'amount' => $amount,
          'due_date' => $installment->due_date,
        ];
      } elseif ($isDue) {
        // This installment is due but not fully paid — it's the next one
        $nextAmount = $amount - $balance;
        $balance = 0;

        // If this is a PAYMENT installment, also add any unpaid due FEE amounts
        if ($installment->type === 'PAYMENT') {
          $unpaidFees = $installments
            ->where('type', 'FEE')
            ->filter(
              fn($feeInstallment) =>
              Carbon::parse($feeInstallment->due_date)->lte($today) &&
                !collect($paidInstallments)->pluck('installment_id')->contains($feeInstallment->id)
            )
            ->sum(fn($feeInstallment) => $feeInstallment->fee->amount ?? 0);

          $nextAmount += $unpaidFees;
        }

        // Save this as the next thing to pay
        $nextInstallment = [
          'installment_id' => $installment->id,
          'type' => $installment->type,
          'amount_due' => round($nextAmount, 2),
          'due_date' => $installment->due_date,
        ];

        // Add it to the remaining installments list
        $remainingInstallments[] = $nextInstallment;

        // Stop here — future installments are not yet due
        break;
      }
    }

    // Any future installments (due after today) are considered remaining
    $futureInstallments = $installments->filter(fn($i) => Carbon::parse($i->due_date)->gt($today));

    foreach ($futureInstallments as $installment) {
      $amount = $installment->type === 'FEE'
        ? ($installment->fee->amount ?? 0)
        : $installmentAmount;

      $remainingInstallments[] = [
        'installment_id' => $installment->id,
        'type' => $installment->type,
        'amount_due' => round($amount, 2),
        'due_date' => $installment->due_date,
      ];
    }

    // Apply remaining balance to first future installment
    if (count($remainingInstallments)) {
      $remainingInstallments[0]['amount_due'] = round(
        max(0, $remainingInstallments[0]['amount_due'] - $balance),
        2
      );
      $balance = 0;
    }

    // Fallback: set the next unpaid installment even if it's in the future
    if (!$nextInstallment && count($remainingInstallments)) {
      $nextInstallment = $remainingInstallments[0];
    }

    // Final result
    return [
      'paid_installments' => $paidInstallments,
      'remaining_installments' => $remainingInstallments,
      'next_installment' => $nextInstallment,
      'fully_paid' => count($remainingInstallments) === 0,
    ];
  }
}
