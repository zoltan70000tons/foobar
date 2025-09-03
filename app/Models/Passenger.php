<?php

namespace App\Models;

use DB;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Carbon\Carbon;
use Log;
use Str;

/**
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\OnboardCredit[] $onboardCredits
 */
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

  protected $appends = ['full_name', 'empty'];

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

  // Installments mean the due dates for the passenger payments
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

  public function onboardCredits()
  {
    return $this->hasMany(OnboardCredit::class);
  }
  
  public function passengerTokens()
  {
    return $this->hasMany(PassengerToken::class);
  }

  public function getInstallmentStatusAttribute()
  {
    return $this->getInstallmentStatus();
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
  public static function checkSurvivorInActiveBookings(string $survivorNumber, int $eventId, ?int $excludeBookingId = null): bool
  {
   return self::where('survivor_number', $survivorNumber)
        ->whereHas('booking', function ($query) use ($eventId, $excludeBookingId) {
            $query->where('status', '!=', 'CANCELLED')
                  ->where('event_id', $eventId)
                  ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId));
        })
        ->exists();
  }

  /**
   * Get the installment status for a passenger.
   */
  public function getInstallmentStatus(): array
  {
    $balance = $this->passenger_balance ?? 0;
    $today = now()->addDay();

    // Fetch all installments ordered by due date, including associated fee data
    $installments = $this->installments()
      ->with('fee')
      ->orderBy('due_date')
      ->get();

    // Calculate total fee amount (from the fees table)
    $feeAmountTotal = $this->fees()->sum('amount');

    // Count how many PAYMENT-type installments exist
    $paymentInstallments = $installments->where('type', 'PAYMENT');
    $paymentCount = $paymentInstallments->count();

    // Calculate base amount for each PAYMENT installment
    $installmentAmount = $paymentCount > 0
      ? ($this->passenger_allocated_cost - $feeAmountTotal) / $paymentCount
      : 0;

    // Output containers
    $paidInstallments = [];
    $remainingInstallments = [];
    $nextInstallment = null;

    // Loop through all installments in due date order
    foreach ($installments as $installment) {
      // Determine how much this installment is worth
      $amount = $installment->type === 'FEE'
        ? (round($installment->fee->amount, 2) ?? 0)
        : round($installmentAmount, 2);

      $name = $installment->type === 'FEE'
        ? $installment->fee->type : null;

      // Prepare base response object
      $installmentData = [
        'installment_id' => $installment->id,
        'type' => $installment->type,
        'original_amount_due' => $amount,
        'due_date' => $installment->due_date,
        'name' => ucwords($name)
      ];

      // 🔹 Fully paid with available balance
      if (round($balance, 2) >= $amount) {
        $installmentData['amount'] = round($amount, 2);
        $installmentData['status'] = 'Paid';
        $paidInstallments[] = $installmentData;
        $balance -= $amount;
        // 🔹 Partially paid (some balance remaining)
      } elseif (round($balance) > 0) {
        $installmentData['amount_due'] = round($amount - $balance, 2);
        $installmentData['status'] = 'Partially Paid';
        $remainingInstallments[] = $installmentData;

        // Set this as the next installment to be paid
        if (!$nextInstallment) {
          $nextInstallment = $installmentData;
        }

        $balance = 0;
      } else {
        // 🔹 Not paid at all
        $installmentData['amount_due'] = round($amount, 2);
        $installmentData['status'] = 'Unpaid';
        $remainingInstallments[] = $installmentData;

        // Set this as the next installment to be paid (if none set yet)
        if (!$nextInstallment) {
          $nextInstallment = $installmentData;
        }
      }
    }

    // If there are unpaid FEE installments due today or earlier,
    // and they haven't been fully paid, add their amount to the next installment
    if ($nextInstallment && $nextInstallment['type'] === 'PAYMENT') {
      // Filter unpaid FEE installments due today or earlier
      $unpaidDueFees = $installments
        ->where('type', 'FEE')
        ->filter(function ($installment) use ($paidInstallments, $today) {
          return Carbon::parse($installment->due_date)->lte($today)
            && !collect($paidInstallments)->pluck('installment_id')->contains($installment->id);
        })
        ->sum(fn($installment) => $installment->fee->amount ?? 0);

      if ($unpaidDueFees > 0) {
        $nextInstallment['amount_due'] = round(($nextInstallment['amount_due'] ?? 0) + $unpaidDueFees, 2);
        $nextInstallment['added_fees'] = $unpaidDueFees; // Added unpaid fees to the next installment
      }
    }

    // If the next installment is a FEE, check if there's a PAYMENT installment after it
    if ($nextInstallment && $nextInstallment['type'] === 'FEE') {
      // Find the first unpaid PAYMENT installment after this fee
      $nextPayment = collect($remainingInstallments)
        ->first(fn($i) => $i['type'] === 'PAYMENT');

      if ($nextPayment) {
        $nextInstallment['amount_due'] = round(
          ($nextInstallment['amount_due'] ?? 0) + ($nextPayment['amount_due'] ?? 0),
          2
        );
      }
    }


    // Final structured output
    return [
      'paid_installments' => $paidInstallments,               // All installments fully covered
      'remaining_installments' => $remainingInstallments,     // All unpaid or partially paid
      'next_installment' => $nextInstallment,                 // First unpaid one in order
      'fully_paid' => count($remainingInstallments) === 0,    // All covered?
    ];
  }


  public function getFullPaymentStatus(): array
  {
    $amount = round($this->passenger_allocated_cost, 2);
    $totalPaid = round($this->passenger_balance, 2);
    $remainingAmount = round($amount - $totalPaid, 2);

    $status = 'Unpaid';
    if ($totalPaid >= $amount) {
      $status = 'Paid';
    } elseif ($totalPaid > 0 && $totalPaid < $amount) {
      $status = 'Partially Paid';
    }

    return [
      'due_date' => $this->booking->created_at->format('Y-m-d'),
      'amount' => $totalPaid,
      'remaining_amount' => $remainingAmount,
      'fully_paid' => $totalPaid >= $amount,
      'status' => $status,
    ];
  }


  public function getMembership()
  {
    $survivor = SurvivorNumber::where('survivor_number', $this->survivor_number)->first();

    if (!$survivor || !$survivor->user_id) {
      return '';
    }
    $membership = Membership::where('user_id', $survivor->user_id)->first();
    if (!$membership || !$membership->membership_id) {
      return '';
    }
    $membershipType = MembershipType::where('id', $membership->membership_id)->first();
    return $membershipType?->name ?? '';
  }

  public function getOnboardCredit()
  {
    return $this->onboardCredits()->sum('amount');
  }

  public function getRefoundAmount()
  {
    return $this->payments()->where('type', 'REFUND')->sum('amount');
  }
}
