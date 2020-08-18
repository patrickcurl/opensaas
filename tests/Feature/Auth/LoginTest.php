<?php

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('has login page', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200)->assertSeeLivewire('auth.login');
});

it('is redirected if already logged in', function () {
    $user = factory(User::class)->create();
    $this->be($user);
    $this->get(route('login'))->assertRedirect(RouteServiceProvider::HOME);
});

it('is redirected after login', function () {
    $user = factory(User::class)->create(['password' => Hash::make('password')]);

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('authenticate')
        ->assertRedirect(RouteServiceProvider::HOME);
});

it('allows a user to login', function () {
    $user = factory(User::class)->create(['password' => Hash::make('password')]);

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('authenticate');

    $this->assertAuthenticatedAs($user);
});

it('redirects user to home after login', function () {
    $user = factory(User::class)->create(['password' => Hash::make('password')]);

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('authenticate')
        ->assertRedirect(RouteServiceProvider::HOME);
});

it('requires a valid email', function () {
    $user = factory(User::class)->create(['password' => Hash::make('password')]);

    Livewire::test('auth.login')
        ->set('password', 'password')
        ->call('authenticate')
        ->assertHasErrors(['email' => 'required']);
});

it('requires a valid password', function () {
    $user = factory(User::class)->create(['password' => Hash::make('password')]);

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->call('authenticate')
        ->assertHasErrors(['password' => 'required']);
});

it('Shows error for bad login attempt.', function () {
    $user = factory(User::class)->create();

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'bad-password')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertFalse(Auth::check());
});
