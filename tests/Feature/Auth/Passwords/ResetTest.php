<?php

namespace Tests\Feature\Auth\Passwords;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_view_password_reset_page()
    {
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
    }

    /** @test */
    public function can_reset_password()
    {
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
    }

    /** @test */
    public function token_is_required()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => null,
        ])
            ->call('passwordReset')
            ->assertHasErrors(['token' => 'required']);
    }

    /** @test */
    public function email_is_required()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => Str::random(16),
        ])
            ->set('email', null)
            ->call('passwordReset')
            ->assertHasErrors(['email' => 'required']);
    }

    /** @test */
    public function email_is_valid_email()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => Str::random(16),
        ])
            ->set('email', 'email')
            ->call('passwordReset')
            ->assertHasErrors(['email' => 'email']);
    }

    /** @test */
    public function password_is_required()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => Str::random(16),
        ])
            ->set('password', '')
            ->call('passwordReset')
            ->assertHasErrors(['password' => 'required']);
    }

    /** @test */
    public function password_is_minimum_of_eight_characters()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => Str::random(16),
        ])
            ->set('password', 'secret')
            ->call('passwordReset')
            ->assertHasErrors(['password' => 'min']);
    }

    /** @test */
    public function password_matches_password_confirmation()
    {
        Livewire::test('auth.passwords.reset', [
            'token' => Str::random(16),
        ])
            ->set('password', 'new-password')
            ->set('password_confirmation', 'not-new-password')
            ->call('passwordReset')
            ->assertHasErrors(['password' => 'confirmed']);
    }
}
