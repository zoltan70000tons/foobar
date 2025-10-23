<?php

use App\Models\User;
use App\Models\Booking;
use App\Models\Comment;
use Database\Seeders\BookingSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

it('creates a comment for a seeded booking and checks relations', function () {
    // Seed bookings (BookingSeeder)
    $this->seed(BookingSeeder::class);
    $booking = Booking::first();
    $user = User::first();

    expect($booking)->not->toBeNull();
    expect($user)->not->toBeNull();

    /** @var Comment $comment */
    $comment = Comment::create([
        'booking_id' => $booking->id,
        'user_id'    => $user->id,
        'comment'    => 'Test comment generated for test.',
    ]);


    expect($comment->id)->not->toBeNull();
    $comment->load('user', 'booking');


    expect($comment->user)->not->toBeNull();
    expect($comment->user->id)->toBe($user->id);
    expect($comment->user->getAttributes())->not->toHaveKey('email'); 
    expect($comment->user->username)->toBe($user->username);


    expect($comment->booking)->not->toBeNull();
    expect($comment->booking->id)->toBe($booking->id);
});
