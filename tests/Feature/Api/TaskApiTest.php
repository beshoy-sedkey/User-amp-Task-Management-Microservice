<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create(['username' => 'testuser']);
    }

    public function test_create_task_with_valid_data()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Task created successfully',
            ])
            ->assertJsonStructure([
                'data' => ['id', 'title', 'description', 'status', 'user_id', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_task_with_default_status()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'status' => 'pending',
                ],
            ]);
    }

    public function test_create_task_without_title()
    {
        $response = $this->postJson('/api/tasks', [
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Title is required',
            ]);
    }

    public function test_create_task_without_user_id()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'User ID is required',
            ]);
    }

    public function test_create_task_with_non_existent_user()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'user_id' => 999,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'User not found',
            ]);
    }

    public function test_create_task_with_invalid_status()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'Test Task',
            'status' => 'invalid-status',
            'user_id' => $this->user->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid status',
            ]);
    }

    public function test_get_all_tasks()
    {
        Task::create(['title' => 'Task 1', 'user_id' => $this->user->id]);
        Task::create(['title' => 'Task 2', 'user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks?page=1&limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'user_id', 'created_at', 'updated_at'],
                ],
                'pagination' => ['page', 'limit', 'total', 'totalPages'],
            ]);

        $this->assertEquals(2, $response['pagination']['total']);
    }

    public function test_get_tasks_filtered_by_user_id()
    {
        $user2 = User::create(['username' => 'testuser2']);

        Task::create(['title' => 'Task 1', 'user_id' => $this->user->id]);
        Task::create(['title' => 'Task 2', 'user_id' => $this->user->id]);
        Task::create(['title' => 'Task 3', 'user_id' => $user2->id]);

        $response = $this->getJson("/api/tasks?userId={$this->user->id}&page=1&limit=10");

        $response->assertStatus(200);
        $this->assertEquals(2, $response['pagination']['total']);

        foreach ($response['data'] as $task) {
            $this->assertEquals($this->user->id, $task['user_id']);
        }
    }

    public function test_get_tasks_with_invalid_user_filter()
    {
        $response = $this->getJson('/api/tasks?userId=999&page=1&limit=10');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'User not found',
            ]);
    }

    public function test_get_task_by_id()
    {
        $task = Task::create(['title' => 'Test Task', 'user_id' => $this->user->id]);

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $task->id,
                'title' => 'Test Task',
                'user_id' => $this->user->id,
            ]);
    }

    public function test_get_task_by_invalid_id()
    {
        $response = $this->getJson('/api/tasks/999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Task not found',
            ]);
    }

    public function test_update_task_title()
    {
        $task = Task::create(['title' => 'Old Title', 'user_id' => $this->user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'New Title',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task updated successfully',
                'data' => [
                    'title' => 'New Title',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Title',
        ]);
    }

    public function test_update_task_status()
    {
        $task = Task::create(['title' => 'Test Task', 'status' => 'pending', 'user_id' => $this->user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'in-progress',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'status' => 'in-progress',
                ],
            ]);
    }

    public function test_update_task_with_invalid_status()
    {
        $task = Task::create(['title' => 'Test Task', 'user_id' => $this->user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'status' => 'invalid-status',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Invalid status',
            ]);
    }

    public function test_update_task_without_data()
    {
        $task = Task::create(['title' => 'Test Task', 'user_id' => $this->user->id]);

        $response = $this->putJson("/api/tasks/{$task->id}", []);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'At least one field must be provided',
            ]);
    }

    public function test_delete_task()
    {
        $task = Task::create(['title' => 'Test Task', 'user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task deleted successfully',
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_delete_non_existent_task()
    {
        $response = $this->deleteJson('/api/tasks/999');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Task not found',
            ]);
    }
}
