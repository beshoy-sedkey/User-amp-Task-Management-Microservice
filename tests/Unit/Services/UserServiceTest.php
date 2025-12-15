<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\UserService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepositoryContract $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryContract::class);
        $this->userService = new UserService($this->userRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_user_with_username_and_email()
    {
        $data = ['username' => 'john_doe', 'email' => 'john@example.com'];
        $user = new User($data);
        $user->id = 1;

        $this->userRepository
            ->shouldReceive('usernameExists')
            ->with('john_doe')
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('emailExists')
            ->with('john@example.com')
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('create')
            ->with($data)
            ->andReturn($user);

        $result = $this->userService->createUser($data);

        $this->assertEquals('john_doe', $result->username);
        $this->assertEquals('john@example.com', $result->email);
    }

    public function test_create_user_throws_exception_when_no_username_or_email()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Either username or email must be provided');

        $this->userService->createUser([]);
    }

    public function test_create_user_throws_exception_when_username_exists()
    {
        $data = ['username' => 'john_doe'];

        $this->userRepository
            ->shouldReceive('usernameExists')
            ->with('john_doe')
            ->andReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Username already exists');

        $this->userService->createUser($data);
    }

    public function test_create_user_throws_exception_when_email_exists()
    {
        $data = ['email' => 'john@example.com'];

        $this->userRepository
            ->shouldReceive('emailExists')
            ->with('john@example.com')
            ->andReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already exists');

        $this->userService->createUser($data);
    }

    public function test_get_all_users_with_valid_pagination()
    {
        $users = collect([
            new User(['id' => 1, 'username' => 'user1']),
            new User(['id' => 2, 'username' => 'user2']),
        ]);

        $paginator = Mockery::mock('Illuminate\Pagination\Paginator');
        $paginator->shouldReceive('items')->andReturn($users->toArray());

        $this->userRepository
            ->shouldReceive('paginate')
            ->with(1, 10)
            ->andReturn($paginator);

        $result = $this->userService->getAllUsers(1, 10);

        $this->assertNotNull($result);
    }

    public function test_get_all_users_throws_exception_when_page_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Page must be >= 1');

        $this->userService->getAllUsers(0, 10);
    }

    public function test_get_all_users_throws_exception_when_limit_invalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');

        $this->userService->getAllUsers(1, 101);
    }

    public function test_get_user_by_id()
    {
        $user = new User(['id' => 1, 'username' => 'john_doe']);

        $this->userRepository
            ->shouldReceive('findById')
            ->with(1)
            ->andReturn($user);

        $result = $this->userService->getUserById(1);

        $this->assertEquals(1, $result->id);
        $this->assertEquals('john_doe', $result->username);
    }

    public function test_get_user_by_id_returns_null_when_not_found()
    {
        $this->userRepository
            ->shouldReceive('findById')
            ->with(999)
            ->andReturn(null);

        $result = $this->userService->getUserById(999);

        $this->assertNull($result);
    }
}
