<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {}

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query('page', 1);
            $limit = (int) $request->query('limit', 10);
            $userId = $request->query('userId') ? (int) $request->query('userId') : null;

            $tasks = $this->taskService->getAllTasks($page, $limit, $userId);

            return response()->json([
                'data' => $tasks->items(),
                'pagination' => [
                    'page' => $tasks->currentPage(),
                    'limit' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'totalPages' => $tasks->lastPage(),
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->only(['title', 'description', 'status', 'user_id']);

            $task = $this->taskService->createTask($data);

            return response()->json([
                'message' => 'Task created successfully',
                'data' => $task,
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Display the specified task.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $taskId = (int) $id;

            if ($taskId < 1) {
                return response()->json(['error' => 'Invalid task ID'], 400);
            }

            $task = $this->taskService->getTaskById($taskId);

            if (!$task) {
                return response()->json(['error' => 'Task not found'], 404);
            }

            return response()->json($task, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $taskId = (int) $id;

            if ($taskId < 1) {
                return response()->json(['error' => 'Invalid task ID'], 400);
            }

            $data = $request->only(['title', 'description', 'status']);

            $task = $this->taskService->updateTask($taskId, $data);

            return response()->json([
                'message' => 'Task updated successfully',
                'data' => $task,
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $taskId = (int) $id;

            if ($taskId < 1) {
                return response()->json(['error' => 'Invalid task ID'], 400);
            }

            $this->taskService->deleteTask($taskId);

            return response()->json([
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
