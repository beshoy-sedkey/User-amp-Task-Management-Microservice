# User & Task Management API - Laravel Implementation

A production-ready RESTful API built with Laravel featuring Repository Design Pattern, Service Layer, Unit Tests, and Feature Tests.

## 🎯 Project Overview

This is a backend REST API microservice demonstrating professional Laravel development practices including:
- **Repository Design Pattern** - Abstraction layer for data access
- **Service Layer** - Business logic separation
- **Dependency Injection** - Loose coupling through IoC container
- **Unit Tests** - Service layer testing with mocks
- **Feature Tests** - API endpoint testing with real database

## ✨ Features

- **8 REST Endpoints** - Complete CRUD operations for users and tasks
- **User Management** - Create, list, and retrieve users
- **Task Management** - Create, list, retrieve, update, and delete tasks
- **Repository Pattern** - Clean data access abstraction
- **Service Layer** - Business logic and validation
- **Input Validation** - Comprehensive validation in services
- **Error Handling** - Consistent error responses
- **Pagination** - Configurable pagination (1-100 items)
- **Database Relationships** - Proper foreign keys with cascade delete
- **Type Safety** - Full PHP type hints throughout
- **Comprehensive Tests** - 51+ test cases (20 unit + 31 feature tests)

## 📋 Requirements

- PHP 8.1+
- Composer
- Laravel 11
- SQLite (for testing) / MySQL (for production)

## 🚀 Quick Start

### 1. Install Dependencies
```bash
composer install
```

### 2. Generate Application Key
```bash
php artisan key:generate
```

### 3. Run Migrations (Development)
For development with MySQL, configure `.env` and run:
```bash
php artisan migrate
```

### 4. Start Development Server
```bash
php artisan serve
```

The API server will start on `http://localhost:8000`

## 📚 API Endpoints

### User Management (3 endpoints)
- `POST /api/users` - Create a new user
- `GET /api/users` - Get all users (with pagination)
- `GET /api/users/:id` - Get specific user by ID

### Task Management (5 endpoints)
- `POST /api/tasks` - Create a new task (linked to user)
- `GET /api/tasks` - Get all tasks (with optional userId filter and pagination)
- `GET /api/tasks/:id` - Get specific task by ID
- `PUT /api/tasks/:id` - Update task (title, description, status)
- `DELETE /api/tasks/:id` - Delete task

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Unit Tests Only
```bash
php artisan test tests/Unit
```

### Run Feature Tests Only
```bash
php artisan test tests/Feature
```

### Run Specific Test Class
```bash
php artisan test tests/Feature/Api/UserApiTest
```

### Test Coverage
The project includes:
- **20 Unit Tests** - Service layer validation and business logic
- **31 Feature Tests** - API endpoint testing with real database interactions

## 🏗️ Architecture

### Directory Structure
```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── UserController.php      # User API endpoints
│           └── TaskController.php      # Task API endpoints
├── Models/
│   ├── User.php                        # User model with relationships
│   └── Task.php                        # Task model with relationships
├── Repositories/
│   ├── Contracts/
│   │   ├── UserRepositoryContract.php  # User repository interface
│   │   └── TaskRepositoryContract.php  # Task repository interface
│   ├── UserRepository.php              # User repository implementation
│   └── TaskRepository.php              # Task repository implementation
├── Services/
│   ├── UserService.php                 # User business logic
│   └── TaskService.php                 # Task business logic
└── Providers/
    └── RepositoryServiceProvider.php   # Service container bindings

database/
├── migrations/
│   ├── 2014_10_12_000000_create_users_table.php
│   └── 2025_12_15_120752_create_tasks_table.php
└── factories/

routes/
└── api.php                             # API route definitions

tests/
├── Unit/
│   └── Services/
│       ├── UserServiceTest.php         # User service unit tests
│       └── TaskServiceTest.php         # Task service unit tests
└── Feature/
    └── Api/
        ├── UserApiTest.php             # User API feature tests
        └── TaskApiTest.php             # Task API feature tests
```

### Design Patterns

#### Repository Pattern
Repositories abstract data access logic:
```php
// Contract defines interface
interface UserRepositoryContract {
    public function create(array $data): User;
    public function paginate(int $page, int $limit): Paginator;
    public function findById(int $id): ?User;
}

// Implementation handles database queries
class UserRepository implements UserRepositoryContract {
    public function create(array $data): User {
        return User::create($data);
    }
}
```

#### Service Layer
Services contain business logic and validation:
```php
class UserService {
    public function createUser(array $data): User {
        // Validate data
        if (empty($data['username']) && empty($data['email'])) {
            throw new InvalidArgumentException('...');
        }
        
        // Check duplicates
        if ($this->userRepository->usernameExists($data['username'])) {
            throw new InvalidArgumentException('Username already exists');
        }
        
        // Create user
        return $this->userRepository->create($data);
    }
}
```

#### Dependency Injection
Controllers receive dependencies through constructor:
```php
class UserController extends Controller {
    public function __construct(private UserService $userService) {}
    
    public function store(Request $request): JsonResponse {
        $user = $this->userService->createUser($request->all());
        return response()->json($user, 201);
    }
}
```

## 🗄️ Database Schema

### User Table
| Field | Type | Constraints |
|-------|------|-----------|
| id | Integer | Primary Key, Auto-increment |
| username | String | Unique, Nullable |
| email | String | Unique, Nullable |
| created_at | Timestamp | Not null |
| updated_at | Timestamp | Not null |

### Task Table
| Field | Type | Constraints |
|-------|------|-----------|
| id | Integer | Primary Key, Auto-increment |
| title | String | Not null |
| description | Text | Nullable |
| status | Enum | pending/in-progress/completed |
| user_id | Integer | Foreign Key, Not null |
| created_at | Timestamp | Not null |
| updated_at | Timestamp | Not null |

## 🔍 Validation Rules

### User Creation
- Either `username` or `email` must be provided
- Username and email must be unique
- Email must be valid format (if provided)

### Task Creation
- `title` is required (1-255 characters)
- `user_id` is required and must reference an existing user
- `status` must be one of: `pending`, `in-progress`, `completed`
- `description` is optional

### Task Update
- At least one field must be provided
- `status` must be one of: `pending`, `in-progress`, `completed`

### Pagination
- `page` must be >= 1
- `limit` must be >= 1 and <= 100

## 📝 API Examples

### Create a User
```bash
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "username": "john_doe",
    "email": "john@example.com"
  }'
```

### Get All Users
```bash
curl -X GET "http://localhost:8000/api/users?page=1&limit=10"
```

### Create a Task
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Complete project",
    "description": "Finish the API",
    "status": "pending",
    "user_id": 1
  }'
```

### Get Tasks for a User
```bash
curl -X GET "http://localhost:8000/api/tasks?userId=1&page=1&limit=10"
```

### Update Task Status
```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "in-progress"}'
```

### Delete Task
```bash
curl -X DELETE http://localhost:8000/api/tasks/1
```

## 🔐 Error Handling

All error responses follow a consistent format:

```json
{
  "error": "Error message"
}
```

### HTTP Status Codes
- `200` - OK (successful GET, PUT, DELETE)
- `201` - Created (successful POST)
- `400` - Bad Request (validation errors)
- `404` - Not Found (resource not found)
- `500` - Internal Server Error (server-side error)

## 🛠️ Available Commands

```bash
# Development
php artisan serve                    # Start development server

# Database
php artisan migrate                 # Run migrations
php artisan migrate:rollback        # Rollback migrations
php artisan migrate:fresh           # Fresh migration

# Testing
php artisan test                    # Run all tests
php artisan test tests/Unit         # Run unit tests
php artisan test tests/Feature      # Run feature tests

# Code Quality
php artisan tinker                  # Interactive shell
php artisan list                    # List all commands
```

## 📊 Test Coverage

### Unit Tests (20 tests)
- UserService: 8 tests
  - Create user with valid data
  - Create user validation errors
  - Get all users with pagination
  - Get user by ID
  
- TaskService: 12 tests
  - Create task with valid data
  - Create task validation errors
  - Update task with valid data
  - Delete task
  - Task status validation

### Feature Tests (31 tests)
- UserApi: 15 tests
  - Create user endpoints
  - Get users with pagination
  - Get user by ID
  - Error handling
  
- TaskApi: 16 tests
  - Create task endpoints
  - Get tasks with filtering
  - Update task endpoints
  - Delete task endpoints
  - Error handling

## 🔄 Development Workflow

1. **Define Models** - Create models with relationships
2. **Create Migrations** - Define database schema
3. **Create Repositories** - Define contracts and implementations
4. **Create Services** - Implement business logic
5. **Create Controllers** - Implement API endpoints
6. **Write Tests** - Unit and feature tests
7. **Run Tests** - Verify all functionality

## 📖 Key Files

- `app/Http/Controllers/Api/UserController.php` - User API endpoints
- `app/Http/Controllers/Api/TaskController.php` - Task API endpoints
- `app/Services/UserService.php` - User business logic
- `app/Services/TaskService.php` - Task business logic
- `app/Repositories/UserRepository.php` - User data access
- `app/Repositories/TaskRepository.php` - Task data access
- `tests/Unit/Services/UserServiceTest.php` - User service tests
- `tests/Unit/Services/TaskServiceTest.php` - Task service tests
- `tests/Feature/Api/UserApiTest.php` - User API tests
- `tests/Feature/Api/TaskApiTest.php` - Task API tests

## 🎓 Learning Resources

This project demonstrates:
- Repository Design Pattern for data abstraction
- Service Layer for business logic separation
- Dependency Injection for loose coupling
- Unit testing with Mockery
- Feature testing with Laravel's testing utilities
- RESTful API best practices
- Error handling and validation
- Database relationships and migrations

## 🚢 Production Deployment

### Environment Configuration
Create `.env` file with production database:
```
DB_CONNECTION=mysql
DB_HOST=your-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

### Build for Production
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Run Migrations
```bash
php artisan migrate --force
```

---

**Version**: 1.0.0  
**Type**: Backend REST API (Laravel)  
**Architecture**: Repository + Service Layer  
**Status**: Production Ready ✅
