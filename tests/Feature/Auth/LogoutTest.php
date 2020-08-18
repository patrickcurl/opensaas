<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

it('lets an authenticated user logout.', function () {
    $user = factory(User::class)->create();
    $this->be($user);

    $this->post(route('logout'))
        ->assertRedirect('/');

    $this->assertFalse(Auth::check());
});
it('does not let a guest logout', function () {
    $this->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertFalse(Auth::check());
});
