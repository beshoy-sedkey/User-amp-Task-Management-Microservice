<?php

namespace Tests\Unit\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\TaskService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class TaskServiceTest extends TestCase
{
    private TaskService $taskService;
    private TaskRepositoryContract $taskRepository;
    private UserRepositoryContract $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->taskRepository = Mockery::mock(TaskRepositoryContract::class);
        $this->userRepository = Mockery::mock(UserRepositoryContract::class);
        $this->taskService = new TaskService($this->taskRepository, $this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_task_with_valid_data()
    {
        $data = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending',
            'user_id' => 1,
        ];

        $user = new User(['id' => 1]);
        $task = new Task($data);
        $task->id = 1;

        $this->userRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($user);

        $this->taskRepository
            ->shouldReceive('create')
            ->with($data)
            ->andReturn($task);

        $result = $this->taskService->createTask($data);

        $this->assertEquals('Test Task', $result->title);
        $this->assertEquals('pending', $result->status);
    }

    public function test_create_task_throws_exception_when_title_missing()
    {
        $data = ['user_id' => 1];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Title is required');

        $this->taskService->createTask($data);
    }

    public function test_create_task_throws_exception_when_user_id_missing()
    {
        $data = ['title' => 'Test Task'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User ID is required');

        $this->taskService->createTask($data);
    }

    public function test_create_task_throws_exception_when_user_not_found()
    {
        $data = ['title' => 'Test Task', 'user_id' => 999];

        $this->userRepository
            ->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('User not found');

        $this->taskService->createTask($data);
    }

    public function test_create_task_throws_exception_when_status_invalid()
    {
        $data = [
            'title' => 'Test Task',
            'status' => 'invalid-status',
            'user_id' => 1,
        ];

        $user = new User(['id' => 1]);

        $this->userRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($user);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status');

        $this->taskService->createTask($data);
    }

    public function test_update_task_with_valid_data()
    {
        $task = new Task(['id' => 1, 'title' => 'Old Title', 'status' => 'pending']);
        $task->id = 1;

        $updateData = ['title' => 'New Title', 'status' => 'in-progress'];

        $this->taskRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($task);

        $updatedTask = new Task(['id' => 1, 'title' => 'New Title', 'status' => 'in-progress']);
        $updatedTask->id = 1;

        $this->taskRepository
            ->shouldReceive('update')
            ->with(1, $updateData)
            ->andReturn($updatedTask);

        $result = $this->taskService->updateTask(1, $updateData);

        $this->assertEquals('New Title', $result->title);
        $this->assertEquals('in-progress', $result->status);
    }

    public function test_update_task_throws_exception_when_not_found()
    {
        $this->taskRepository
            ->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Task not found');

        $this->taskService->updateTask(999, ['title' => 'New Title']);
    }

    public function test_update_task_throws_exception_when_no_data_provided()
    {
        $task = new Task(['id' => 1]);
        $task->id = 1;

        $this->taskRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($task);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one field must be provided');

        $this->taskService->updateTask(1, []);
    }

    public function test_delete_task()
    {
        $task = new Task(['id' => 1]);
        $task->id = 1;

        $this->taskRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($task);

        $this->taskRepository
            ->shouldReceive('delete')
            ->with(1)
            ->andReturn(true);

        $result = $this->taskService->deleteTask(1);

        $this->assertTrue($result);
    }

    public function test_delete_task_throws_exception_when_not_found()
    {
        $this->taskRepository
            ->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Task not found');

        $this->taskService->deleteTask(999);
    }
}
