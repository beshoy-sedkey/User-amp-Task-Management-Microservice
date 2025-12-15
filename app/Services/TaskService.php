<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;

class TaskService
{
    public function __construct(
        private TaskRepositoryContract $taskRepository,
        private UserRepositoryContract $userRepository
    ) {}

    /**
     * Create a new task with validation.
     */
    public function createTask(array $data): Task
    {
        // Validate required fields
        if (empty($data['title'])) {
            throw new InvalidArgumentException('Title is required');
        }

        if (empty($data['user_id'])) {
            throw new InvalidArgumentException('User ID is required');
        }

        // Check if user exists
        if (!$this->userRepository->findById($data['user_id'])) {
            throw new InvalidArgumentException('User not found');
        }

        // Validate status if provided
        if (!empty($data['status'])) {
            $validStatuses = ['pending', 'in-progress', 'completed'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new InvalidArgumentException('Invalid status. Must be one of: pending, in-progress, completed');
            }
        }

        return $this->taskRepository->create($data);
    }

    /**
     * Get all tasks with pagination and optional user filter.
     */
    public function getAllTasks(int $page = 1, int $limit = 10, ?int $userId = null): Paginator
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be >= 1');
        }

        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Limit must be between 1 and 100');
        }

        // Check if user exists when filtering by userId
        if ($userId !== null && !$this->userRepository->findById($userId)) {
            throw new InvalidArgumentException('User not found');
        }

        return $this->taskRepository->paginate($page, $limit, $userId);
    }

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $id): ?Task
    {
        return $this->taskRepository->findById($id);
    }

    /**
     * Update a task with validation.
     */
    public function updateTask(int $id, array $data): ?Task
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            throw new InvalidArgumentException('Task not found');
        }

        // Check if at least one field is provided
        if (empty($data)) {
            throw new InvalidArgumentException('At least one field must be provided');
        }

        // Validate status if provided
        if (!empty($data['status'])) {
            $validStatuses = ['pending', 'in-progress', 'completed'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new InvalidArgumentException('Invalid status. Must be one of: pending, in-progress, completed');
            }
        }

        return $this->taskRepository->update($id, $data);
    }

    /**
     * Delete a task.
     */
    public function deleteTask(int $id): bool
    {
        $task = $this->taskRepository->findById($id);

        if (!$task) {
            throw new InvalidArgumentException('Task not found');
        }

        return $this->taskRepository->delete($id);
    }
}
