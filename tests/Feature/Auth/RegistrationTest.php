<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can not register because registration is disabled', function () {
    $response = $this->post('/register', [
        'nim' => '1234567890',
        'nama' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    expect(in_array($response->status(), [404, 405]))->toBeTrue();
});