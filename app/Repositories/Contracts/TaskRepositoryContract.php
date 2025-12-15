<?php

namespace App\Repositories\Contracts;

use App\Models\Task;
use Illuminate\Pagination\Paginator;

interface TaskRepositoryContract
{
    /**
     * Create a new task.
     */
    public function create(array $data): Task;

    /**
     * Get all tasks with pagination.
     */
    public function paginate(int $page = 1, int $limit = 10, ?int $userId = null): Paginator;

    /**
     * Get a task by ID.
     */
    public function findById(int $id): ?Task;

    /**
     * Update a task.
     */
    public function update(int $id, array $data): ?Task;

    /**
     * Delete a task.
     */
    public function delete(int $id): bool;

    /**
     * Get tasks by user ID.
     */
    public function getByUserId(int $userId, int $page = 1, int $limit = 10): Paginator;
}
