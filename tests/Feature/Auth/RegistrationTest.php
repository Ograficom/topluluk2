<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertRedirect(route('feed.home', absolute: false));
});

test('new users cannot register when registration is disabled', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect(route('feed.home', absolute: false));
});
