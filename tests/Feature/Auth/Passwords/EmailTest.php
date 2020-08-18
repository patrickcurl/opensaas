<?php

namespace Tests\Feature\Auth\Passwords;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

it('shows the password reset page.', function () {
    $this->get(route('password.request'))
        ->assertSuccessful()
        ->assertSeeLivewire('auth.passwords.email');
});

it('required a user to enter an email', function () {
    Livewire::test('auth.passwords.email')
        ->call('sendResetLinkEmail')
        ->assertHasErrors(['email' => 'required']);
});

it('requires a user to enter a VALID email', function () {
    Livewire::test('auth.passwords.email')
        ->set('email', 'email')
        ->call('sendResetLinkEmail')
        ->assertHasErrors(['email' => 'email']);
});

it('sends an email to those who enter a valid email', function () {
    $user = factory(User::class)->create();

    Livewire::test('auth.passwords.email')
        ->set('email', $user->email)
        ->call('sendResetLinkEmail');

    $this->assertDatabaseHas('password_resets', [
        'email' => $user->email,
    ]);
});
