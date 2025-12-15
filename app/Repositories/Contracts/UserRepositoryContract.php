<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\Paginator;

interface UserRepositoryContract
{
    /**
     * Create a new user.
     */
    public function create(array $data): User;

    /**
     * Get all users with pagination.
     */
    public function paginate(int $page = 1, int $limit = 10): Paginator;

    /**
     * Get a user by ID.
     */
    public function findById(int $id): ?User;

    /**
     * Check if username exists.
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool;

    /**
     * Check if email exists.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool;
}
