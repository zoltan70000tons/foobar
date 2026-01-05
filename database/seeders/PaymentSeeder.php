<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Installment;
use App\Models\Passenger;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Container;
use Faker\Generator;
use Carbon\Carbon;
use Ramsey\Uuid\Type\Decimal;

class PaymentSeeder extends Seeder {
    /**
     * The current Faker instance.
     *
     * @var \Faker\Generator
     */
    protected $faker;
    protected $paymentService;

    /**
     * Create a new seeder instance.
     */
    public function __construct(PaymentService $paymentService) {
        $this->faker = $this->withFaker();
        $this->paymentService = $paymentService;
    }

    /**
     * Get a new Faker instance.
     */
    protected function withFaker() {
        return Container::getInstance()->make(Generator::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void {
        $bookings = Booking::with('passengers.installments')->get(); // Eager load passengers and installments

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $passenger) {
                if ($booking->payment_plan === 'INSTALLMENTS') {
                    $installments = $passenger->installments;
                    $totalInstallments = count($installments);
                    $amountPerInstallment = $booking->cabin->category->price / $totalInstallments;
                    $installmentsToPay = rand(0, $totalInstallments);
                    $payment = 0;
                    for ($i = 0; $i < $installmentsToPay; $i++) {
                        $installment = $installments[$i];
                        $payment += $this->makePayment($passenger->id, $amountPerInstallment);
                    }
                    $passenger = Passenger::find($passenger->id);
                    $passenger->passenger_allocated_cost = $booking->cabin->category->price;
                    $passenger->passenger_balance = $payment;
                    $passenger->save();
                } elseif ($booking->payment_plan === 'PAY_IN_FULL') {
                    $payment = 0;
                    $this->makeFullPayment($passenger, $booking);
                }
            }
        }
    }

    /**
     * Create a payment for an installment.
     */
    private function makePayment(int $passengerId, $amount): float {
        $response = $this->paymentService->processPayment([
            'passenger_id' => $passengerId,
            'BIP_ID' => $this->faker->uuid(),
            'amount' => $amount,
            'type' => 'PAYMENT',
            'source' => 'SYSTEM',
            'transaction_date' => Carbon::now(),
        ]);

        if ($response['success']) {
            return $response['payment']['amount'];
        } else {
            throw new \Exception($response['message']);
        }
    }

    /**
     * Create a full payment for a booking.
     */
    private function makeFullPayment($passenger, $booking): void {
        $paymentType = rand(1, 100) <= 20 ? 'REFUND' : 'PAYMENT';
        $paymentAmount = rand(1, 100) <= 70 ? $booking->cabin->category->price : 0;
        $response = $this->paymentService->processPayment([
            'passenger_id' => $passenger->id,
            'BIP_ID' => $this->faker->uuid(),
            'amount' => $booking->cabin->category->price,
            'type' => $paymentType,
            'source' => 'SYSTEM',
            'transaction_date' => Carbon::now(),
        ]);

        // dd($response);

        if ($response['success']) {
            if ($response['payment']->type == 'REFUND') {
                $passenger->passenger_balance = $paymentAmount - $response['payment']->amount;
            } else {
                $passenger->passenger_balance = $paymentAmount;
            }
            $passenger->save();
        } else {
            throw new \Exception($response['message']);
        }
    }
}
