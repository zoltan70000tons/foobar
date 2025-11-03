<?php

use App\Mail\CustomerVerificationEmail;
use App\Models\Organization;
use App\Models\User;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;

// uses(RefreshDatabase::class);

beforeEach(function () {
  config()->set('app.frontend_url', 'https://frontend.test');
  Organization::factory()->create();
});

test('oauth unverified customer can view verification notice', function () {
  $user = User::factory()
    ->unverified()
    ->create([
      'email' => 'test_' . fake()->safeEmail(),
    ]);

  Passport::actingAs($user, [], 'web');

  $response = $this->get('/oauth/verify-email?language=en');

  $response->assertOk();
});

test('oauth verification link verifies email and redirects to frontend login', function () {
  $user = User::factory()
    ->unverified()
    ->create([
      'email' => 'test_' . fake()->safeEmail(),
    ]);

  $verificationUrl = URL::temporarySignedRoute('oauth.email.verify.link', now()->addMinutes(20), [
    'id' => $user->getKey(),
    'hash' => sha1($user->getEmailForVerification()),
    'language' => 'en',
  ]);

  $response = $this->get($verificationUrl);

  $response->assertRedirect('https://frontend.test/en/login?verified=1');
  expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

test('oauth verification link with invalid hash redirects to error page', function () {
  $user = User::factory()
    ->unverified()
    ->create([
      'email' => 'test_' . fake()->safeEmail(),
    ]);

  $verificationUrl = URL::temporarySignedRoute('oauth.email.verify.link', now()->addMinutes(20), [
    'id' => $user->getKey(),
    'hash' => sha1('invalid-email@example.com'),
    'language' => 'de',
  ]);

  $response = $this->get($verificationUrl);

  $response->assertRedirect(route('oauth.error', ['language' => 'de', 'error' => 'expired']));
  expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('resending oauth verification email queues mailable for unverified user', function () {
  Mail::fake();

  $user = User::factory()
    ->unverified()
    ->create([
      'email' => 'test_' . fake()->safeEmail(),
    ]);

  Passport::actingAs($user, [], 'web');

  $response = $this->postJson(route('oauth.email.verify.resend'), [
    'language' => 'en',
  ]);

  $response->assertOk()->assertJson([
    'status' => 'verification-link-sent',
  ]);

  Mail::assertQueued(CustomerVerificationEmail::class, function (CustomerVerificationEmail $mail) use ($user) {
    return $mail->hasTo($user->email);
  });
});
