<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Enums\ErrorCode;
use Laravel\Passport\Passport;
use App\Mail\CustomerResetPasswordSuccess;

beforeEach(function () {
  config()->set('app.frontend_url', 'https://frontend.test');
  Organization::factory()->create();
  setPermissionsTeamId(1);
});

test('customer can request password reset link', function () {
  Mail::fake();

  $user = User::factory()->createOne([
    'email' => 'test_reset_' . fake()->safeEmail(),
  ]);

  $user->assignRole('Customer');

  $response = $this->postJson('/api/auth/forgot-password', [
    'email' => $user->email,
    'lang' => 'en',
  ]);

  $response->assertOk()->assertJson([
    'message' => __('passwords.sent'),
  ]);

  $this->assertDatabaseHas('password_reset_tokens', [
    'email' => $user->email,
  ]);
});

test('password reset request is rejected for non customer accounts', function () {
  Mail::fake();

  $user = User::factory()->createOne(['email' => 'test_customer_services_' . fake()->safeEmail()]);

  $response = $this->postJson('/api/auth/forgot-password', [
    'email' => $user->email,
  ]);

  $response->assertStatus(401)->assertJsonPath('errorCode', ErrorCode::UNAUTHORIZED->value);
});

test('password reset fails when token does not match stored hash', function () {
  Mail::fake();

  $user = User::factory()->create([
    'email' => 'test_customer_services_' . fake()->safeEmail(),
  ]);
  $user->assignRole('Customer');

  DB::table('password_reset_tokens')->insert([
    'email' => $user->email,
    'token' => Hash::make('some-random-token'),
    'created_at' => Carbon::now(),
  ]);

  $response = $this->postJson('/api/auth/password-reset', [
    'email' => $user->email,
    'token' => 'invalid-token',
    'password' => 'ValidPass123!',
    'password_confirmation' => 'ValidPass123!',
    'lang' => 'en',
  ]);

  $response
    ->assertStatus(400)
    ->assertJsonPath('errorCode', ErrorCode::INVALID_TOKEN->value)
    ->assertJsonPath('errorMessage', __('passwords.token'));
});

test('customer can reset password with valid token and receives confirmation email', function () {
  Mail::fake();

  $user = User::factory()->create([
    'email' => 'test_reset_' . fake()->safeEmail(),
  ]);
  $user->assignRole('Customer');

  DB::table('password_reset_tokens')->insert([
    'email' => $user->email,
    'token' => Hash::make('valid-token'),
    'created_at' => now(),
  ]);

  $response = $this->postJson('/api/auth/password-reset', [
    'email' => $user->email,
    'token' => 'valid-token',
    'password' => 'ValidPass123!',
    'password_confirmation' => 'ValidPass123!',
    'lang' => 'en',
  ]);

  $response->assertOk()->assertJson([
    'message' => __('passwords.reset'),
  ]);

  expect(Hash::check('ValidPass123!', $user->fresh()->password))->toBeTrue();

  Mail::assertQueued(CustomerResetPasswordSuccess::class, function (CustomerResetPasswordSuccess $mail) use ($user) {
    return $mail->hasTo($user->email);
  });
});
