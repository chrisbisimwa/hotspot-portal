<?php

test('home page returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('home page loads welcome view', function () {
    $response = $this->get('/');

    $response->assertViewIs('welcome');
});

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('admin routes require authentication', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});
