<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_cannot_access_admin_routes()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer);
        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_access_admin_users_route()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $response = $this->get('/admin/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function super_admin_can_access_both_admin_and_admin_users_routes()
    {
        $super = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($super);
        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertStatus(200);
        $responseUsers = $this->get('/admin/users');
        $responseUsers->assertStatus(200);
    }
}
