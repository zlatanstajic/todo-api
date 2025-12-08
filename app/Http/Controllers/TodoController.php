<?php

namespace App\Http\Controllers;

use App\Exceptions\Todo\TodoDeleteFailedException;
use App\Exceptions\Todo\TodoNotFoundException;
use App\Http\Resources\TodoResource;
use App\Services\TodoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Todo Controller
 *
 * @package App\Http\Controllers
 */
class TodoController extends Controller
{
    /**
     * @param TodoService $todoService
     */
    public function __construct(readonly TodoService $todoService)
    {
        //
    }

    /**
     * Get all todos.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            return $this->successResponse(
                TodoResource::collection(
                    $this->todoService->getAllTodos()
                )
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Get a specific todo by ID.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $todo = $this->todoService->getTodoById($id);

            if (!$todo) {
                throw new TodoNotFoundException();
            }

            return $this->successResponse(new TodoResource($todo));
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Create a new todo.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'title'       => 'required|string|max:255',
                'description' => 'nullable|string',
                'completed'   => 'boolean',
            ]);

            $data['user_id'] = $request->user()->id;

            return $this->successResponse(
                data: new TodoResource($this->todoService->createTodo($data)),
                code: Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Update an existing todo.
     *
     * @param Request $request
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'title'       => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'completed'   => 'boolean',
            ]);

            return $this->successResponse(
                new TodoResource($this->todoService->updateTodo($id, $data))
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete a todo.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            if (!$this->todoService->deleteTodo($id)) {
                throw new TodoDeleteFailedException();
            }

            return $this->successResponse();
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
