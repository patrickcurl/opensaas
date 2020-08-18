<?php

it('has login page', function () {
    $response = $this->get(route('login'));

    $response->assertStatus(200)->assertSeeLivewire('auth.login');
});
