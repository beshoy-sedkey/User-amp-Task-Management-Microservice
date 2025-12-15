<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_user_with_username_and_email()
    {
        $response = $this->postJson('/api/users', [
            'username' => 'john_doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'username', 'email', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'john_doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_create_user_with_username_only()
    {
        $response = $this->postJson('/api/users', [
            'username' => 'jane_doe',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'username' => 'jane_doe',
        ]);
    }

    public function test_create_user_with_email_only()
    {
        $response = $this->postJson('/api/users', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_create_user_without_username_or_email()
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Either username or email must be provided',
            ]);
    }

    public function test_create_user_with_duplicate_username()
    {
        User::create(['username' => 'john_doe']);

        $response = $this->postJson('/api/users', [
            'username' => 'john_doe',
            'email' => 'different@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Username already exists',
            ]);
    }

    public function test_create_user_with_duplicate_email()
    {
        User::create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/users', [
            'username' => 'different_user',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Email already exists',
            ]);
    }

    public function test_get_all_users()
    {
        User::create(['username' => 'user1']);
        User::create(['username' => 'user2']);
        User::create(['username' => 'user3']);

        $response = $this->getJson('/api/users?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'username', 'email', 'created_at', 'updated_at'],
                ],
                'pagination' => ['page', 'limit', 'total', 'totalPages'],
            ]);

        $this->assertEquals(3, $response['pagination']['total']);
    }

    public function test_get_users_with_pagination()
    {
        for ($i = 1; $i <= 15; $i++) {
            User::create(['username' => "user{$i}"]);
        }

        $response = $this->getJson('/api/users?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'username'],
                ],
                'pagination' => ['page', 'limit', 'total', 'totalPages'],
            ]);

        $this->assertEquals(1, $response['pagination']['page']);
        $this->assertEquals(10, $response['pagination']['limit']);
        $this->assertEquals(15, $response['pagination']['total']);
        $this->assertEquals(2, $response['pagination']['totalPages']);
    }

    public function test_get_users_with_invalid_page()
    {
        $response = $this->getJson('/api/users?page=0&limit=10');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Page must be >= 1',
            ]);
    }

    public function test_get_users_with_invalid_limit()
    {
        $response = $this->getJson('/api/users?page=1&limit=101');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Limit must be between 1 and 100',
            ]);
    }

    public function test_get_user_by_id()
    {
        $user = User::create(['username' => 'john_doe', 'email' => 'john@example.com']);

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'username' => 'john_doe',
                'email' => 'john@example.com',
            ]);
    }

    public function test_get_user_by_invalid_id()
    {
        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'User not found',
            ]);
    }

    public function test_get_user_with_invalid_id_format()
    {
        $response = $this->getJson('/api/users/invalid');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid user ID',
            ]);
    }
}
