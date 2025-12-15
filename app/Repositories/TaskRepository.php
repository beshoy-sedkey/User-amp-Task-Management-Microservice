<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryContract;
use Illuminate\Pagination\Paginator;

class TaskRepository implements TaskRepositoryContract
{
    /**
     * Create a new task.
     */
    public function create(array $data): Task
    {
        return Task::create($data);
    }

    /**
     * Get all tasks with pagination.
     */
    public function paginate(int $page = 1, int $limit = 10, ?int $userId = null): Paginator
    {
        $query = Task::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get a task by ID.
     */
    public function findById(int $id): ?Task
    {
        return Task::find($id);
    }

    /**
     * Update a task.
     */
    public function update(int $id, array $data): ?Task
    {
        $task = Task::find($id);

        if ($task) {
            $task->update($data);
        }

        return $task;
    }

    /**
     * Delete a task.
     */
    public function delete(int $id): bool
    {
        $task = Task::find($id);

        if ($task) {
            return $task->delete();
        }

        return false;
    }

    /**
     * Get tasks by user ID.
     */
    public function getByUserId(int $userId, int $page = 1, int $limit = 10): Paginator
    {
        return Task::where('user_id', $userId)->paginate($limit, ['*'], 'page', $page);
    }
}
