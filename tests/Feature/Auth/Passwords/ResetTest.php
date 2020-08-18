<?php

namespace Tests\Feature\Auth\Passwords;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

it('shows password reset page', function () {
    $user = factory(User::class)->create();

    $token = Str::random(16);

    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => Hash::make($token),
        'created_at' => Carbon::now(),
    ]);

    $this->get(route('password.reset', [
        'email' => $user->email,
        'token' => $token,
    ]))
        ->assertSuccessful()
        ->assertSeeLivewire('auth.passwords.reset');
});

it('can reset password', function () {
    $user = factory(User::class)->create();

    $token = Str::random(16);

    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => Hash::make($token),
        'created_at' => Carbon::now(),
    ]);

    Livewire::test('auth.passwords.reset', [
        'token' => $token,
    ])
        ->set('email', $user->email)
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('passwordReset');

    $this->assertTrue(Auth::attempt([
        'email' => $user->email,
        'password' => 'new-password',
    ]));
});

it('requires that token be set', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => null,
    ])
        ->call('passwordReset')
        ->assertHasErrors(['token' => 'required']);
});

it('requires an email', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => Str::random(16),
    ])
        ->set('email', null)
        ->call('passwordReset')
        ->assertHasErrors(['email' => 'required']);
});

it('makes sure email is valid', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => Str::random(16),
    ])
        ->set('email', 'email')
        ->call('passwordReset')
        ->assertHasErrors(['email' => 'email']);
});

it('requires a password', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => Str::random(16),
    ])
        ->set('password', '')
        ->call('passwordReset')
        ->assertHasErrors(['password' => 'required']);
});

it('makes sure password is at least 8 characters', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => Str::random(16),
    ])
        ->set('password', 'secret')
        ->call('passwordReset')
        ->assertHasErrors(['password' => 'min']);
});

it('checks that password and password_confirmation match', function () {
    Livewire::test('auth.passwords.reset', [
        'token' => Str::random(16),
    ])
        ->set('password', 'new-password')
        ->set('password_confirmation', 'not-new-password')
        ->call('passwordReset')
        ->assertHasErrors(['password' => 'confirmed']);
});
