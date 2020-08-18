<?php

namespace Tests\Feature\Auth\Passwords;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

beforeEach(function () {
    Route::get('/must-be-confirmed', function () {
        return 'You must be confirmed to see this page.';
    })->middleware(['web', 'password.confirm']);
});

it('checks that user must be confirmed before visiting a protected page', function () {
    $user = factory(User::class)->create();
    $this->be($user);

    $this->get('/must-be-confirmed')
        ->assertRedirect(route('password.confirm'));

    $this->followingRedirects()
        ->get('/must-be-confirmed')
        ->assertSeeLivewire('auth.passwords.confirm');
});

it('checks that user entered a password.', function () {
    Livewire::test('auth.passwords.confirm')
        ->call('confirm')
        ->assertHasErrors(['password' => 'required']);
});

it('checks that user entered the CORRECT password for their account.', function () {
    $user = factory(User::class)->create([
        'password' => Hash::make('password'),
    ]);

    Livewire::test('auth.passwords.confirm')
        ->set('password', 'not-password')
        ->call('confirm')
        ->assertHasErrors(['password' => 'password']);
});

it('redirects a user after they confirm their account', function () {
    $user = factory(User::class)->create([
        'password' => Hash::make('password'),
    ]);

    $this->be($user);

    $this->withSession(['url.intended' => '/must-be-confirmed']);

    Livewire::test('auth.passwords.confirm')
        ->set('password', 'password')
        ->call('confirm')
        ->assertRedirect('/must-be-confirmed');
});
