<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

it('shows the verification page', function () {
    $user = factory(User::class)->create([
        'email_verified_at' => null,
    ]);

    Auth::login($user);

    $this->get(route('verification.notice'))
        ->assertSuccessful()
        ->assertSeeLivewire('auth.verify');
});

it('can resend verification emails', function () {
    $user = factory(User::class)->create();

    Livewire::actingAs($user);

    Livewire::test('auth.verify')
        ->call('resend')
        ->assertSessionHas('resent', true);
});

it('can verify user', function () {
    $user = factory(User::class)->create([
        'email_verified_at' => null,
    ]);

    Auth::login($user);

    $url = URL::temporarySignedRoute('verification.verify', Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)), [
        'id' => $user->getKey(),
        'hash' => sha1($user->getEmailForVerification()),
    ]);

    $this->get($url)
        ->assertRedirect(RouteServiceProvider::HOME);

    $this->assertTrue($user->hasVerifiedEmail());
});
