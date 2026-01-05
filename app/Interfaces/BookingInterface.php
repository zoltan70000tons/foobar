<?php

namespace App\Interfaces;

use App\Models\Booking;
use App\Models\Cabin;

interface BookingInterface {
    function getAll();
    function find($id);
    function findByCode($code);
    function save(array $data): ?Booking;
    function update(array $data, $id);
    function delete($id);
    function addTags(Booking $booking, $tags);
    function getByTag($tag, $keyword = null);
    function getByStatus(
        $eventId,
        $status,
        $keyword = null,
        ?int $perPage = 10,
        ?string $sortKey = 'created_at',
        ?string $sortDirection = 'asc',
        $dateRange = null,
        ?array $tags = [],
    );
    function changeCabin(Booking $booking, Cabin $cabin);
    function changeCode(Booking $booking, $new_code);
    function changeStatus(Booking $booking, $status);
    function addComment(Booking $booking, $comment);
    function cancel(Booking $booking);
    function createBooking(
        array $bookingData,
        array $passengerData,
        ?Cabin $cabin = null,
        ?int $reservation_id = null,
    ): array;
    function changePaymentPlan(Booking $booking, string $payment_plan, int $number_of_installments);
}
