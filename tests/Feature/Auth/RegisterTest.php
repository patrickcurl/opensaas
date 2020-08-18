<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

it('asserts that registration page has livewire component.', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSeeLivewire('auth.register');
});

it('is redirected if user is already logged in.', function () {
    $user = factory(User::class)->create();

    $this->be($user);

    $this->get(route('register'))
        ->assertRedirect(RouteServiceProvider::HOME);
});

it('allows a user to register', function () {
    Livewire::test('auth.register')
        ->set('name', 'Tall Stack')
        ->set('email', 'tallstack@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('signup')
        ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertTrue(User::whereEmail('tallstack@example.com')->exists());
    $this->assertEquals('tallstack@example.com', Auth::user()->email);
});

it('checks that name is required', function () {
    Livewire::test('auth.register')
        ->set('name', '')
        ->call('signup')
        ->assertHasErrors(['name' => 'required']);
});

it('checks that email is required', function () {
    Livewire::test('auth.register')
        ->set('email', '')
        ->call('signup')
        ->assertHasErrors(['email' => 'required']);
});

it('checks that email is a valid email', function () {
    Livewire::test('auth.register')
        ->set('email', 'tallstack')
        ->call('signup')
        ->assertHasErrors(['email' => 'email']);
});

it('checks that email is unique', function () {
    factory(User::class)->create(['email' => 'tallstack@example.com']);

    Livewire::test('auth.register')
        ->set('email', 'tallstack@example.com')
        ->call('signup')
        ->assertHasErrors(['email' => 'unique']);
});

it('checks that user sees email has already been taken error', function () {
    factory(User::class)->create(['email' => 'tallstack@example.com']);

    Livewire::test('auth.register')
        ->set('email', 'smallstack@gmail.com')
        ->assertHasNoErrors()
        ->set('email', 'tallstack@example.com')
        ->call('signup')
        ->assertHasErrors(['email' => 'unique']);
});

it('checks that password is required', function () {
    Livewire::test('auth.register')
        ->set('password', '')
        ->set('password_confirmation', 'password')
        ->call('signup')
        ->assertHasErrors(['password' => 'required']);
});

it('checks that password is at least 8 characters', function () {
    Livewire::test('auth.register')
        ->set('password', 'secret')
        ->set('password_confirmation', 'secret')
        ->call('signup')
        ->assertHasErrors(['password' => 'min']);
});

it('checks that password and password_confirmation match', function () {
    Livewire::test('auth.register')
        ->set('email', 'tallstack@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'not-password')
        ->call('signup')
        ->assertHasErrors(['password' => 'confirmed']);
});
