<?php

test('homepage returns 200 status', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
});

test('homepage contains welcome content', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    // TODO: Update assertions when actual homepage is implemented
    $response->assertSee('Laravel');
});