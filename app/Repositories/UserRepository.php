<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Pagination\Paginator;

class UserRepository implements UserRepositoryContract
{
    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Get all users with pagination.
     */
    public function paginate(int $page = 1, int $limit = 10): Paginator
    {
        return User::paginate($limit, ['*'], 'page', $page);
    }

    /**
     * Get a user by ID.
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Check if username exists.
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $query = User::where('username', $username);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if email exists.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', $email);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
