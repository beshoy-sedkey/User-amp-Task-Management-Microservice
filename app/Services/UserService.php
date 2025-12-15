<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Pagination\Paginator;
use InvalidArgumentException;

class UserService
{
    public function __construct(
        private UserRepositoryContract $userRepository
    ) {}

    /**
     * Create a new user with validation.
     */
    public function createUser(array $data): User
    {
        // Validate that at least username or email is provided
        if (empty($data['username']) && empty($data['email'])) {
            throw new InvalidArgumentException('Either username or email must be provided');
        }

        // Check for duplicate username
        if (!empty($data['username']) && $this->userRepository->usernameExists($data['username'])) {
            throw new InvalidArgumentException('Username already exists');
        }

        // Check for duplicate email
        if (!empty($data['email']) && $this->userRepository->emailExists($data['email'])) {
            throw new InvalidArgumentException('Email already exists');
        }

        return $this->userRepository->create($data);
    }

    /**
     * Get all users with pagination.
     */
    public function getAllUsers(int $page = 1, int $limit = 10): Paginator
    {
        if ($page < 1) {
            throw new InvalidArgumentException('Page must be >= 1');
        }

        if ($limit < 1 || $limit > 100) {
            throw new InvalidArgumentException('Limit must be between 1 and 100');
        }

        return $this->userRepository->paginate($page, $limit);
    }

    /**
     * Get a user by ID.
     */
    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findById($id);
    }
}
