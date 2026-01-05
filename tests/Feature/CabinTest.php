<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

/**
 * @test
 */
function it_visit_page_of_cabins(): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $response = $this->get('/cabins');
    $response->assertStatus(200);
}
