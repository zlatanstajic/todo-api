<?php

namespace App\Services;

use App\Models\Todo;
use App\Repositories\TodoRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * TodoService handles business logic related to todos.
 *
 * @package App\Services
 */
class TodoService
{
    /**
     * @param TodoRepository $todoRepository
     */
    public function __construct(readonly TodoRepository $todoRepository)
    {
        //
    }

    /**
     * Get all todos.
     *
     * @return Collection
     */
    public function getAllTodos()
    {
        return $this->todoRepository->getAll();
    }

    /**
     * Get todo by ID.
     *
     * @param int $id
     *
     * @return Todo|null
     */
    public function getTodoById(int $id): ?Todo
    {
        return $this->todoRepository->findById($id);
    }

    /**
     * Create a new todo.
     *
     * @param array $data
     *
     * @return Todo
     */
    public function createTodo(array $data): Todo
    {
        return $this->todoRepository->create($data);
    }

    /**
     * Update a todo.
     *
     * @param int $id
     * @param array $data
     *
     * @return Todo
     */
    public function updateTodo(int $id, array $data): Todo
    {
        return $this->todoRepository->update($id, $data);
    }

    /**
     * Delete a todo.
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteTodo(int $id): bool
    {
        return $this->todoRepository->delete($id);
    }
}
