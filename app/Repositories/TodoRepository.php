<?php

namespace App\Repositories;

use App\Exceptions\Todo\TodoNotFoundException;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * Todo Repository
 *
 * @package App\Repositories
 */
class TodoRepository
{
    /**
     * Get all todos.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Todo::all();
    }

    /**
     * Find todo by ID.
     *
     * @param int $id
     *
     * @return Todo|null
     */
    public function findById(int $id): ?Todo
    {
        try {
            return Todo::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Todo item ID {$id} not found.");

            return null;
        }
    }

    /**
     * Create a new todo.
     *
     * @param array $data
     *
     * @return Todo
     */
    public function create(array $data): Todo
    {
        return Todo::create($data);
    }

    /**
     * Update a todo.
     *
     * @param int $id
     * @param array $data
     *
     * @return Todo
     *
     * @throws TodoNotFoundException
     */
    public function update(int $id, array $data): Todo
    {
        $todo = $this->findById($id);

        if (!$todo) {
            throw new TodoNotFoundException();
        }

        $todo->update($data);

        return $todo;
    }

    /**
     * Delete a todo.
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return (bool) Todo::destroy($id);
    }
}
