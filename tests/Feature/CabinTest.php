<?php

namespace Tests\Feature;

use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CabinTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    /**
     * @test
     */
    public function it_visit_page_of_cabins(): void
    {

        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/cabins');
        $response->assertStatus(200);
    }
}
