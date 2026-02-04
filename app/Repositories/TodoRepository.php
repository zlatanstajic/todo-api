<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Exceptions\Todo\TodoNotFoundException;
use App\Models\Todo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * Todo Repository
 */
class TodoRepository
{
    /**
     * The model class name.
     */
    protected string $model = Todo::class;

    /**
     * Get all todos.
     */
    public function getAll(): Collection
    {
        return $this->model::all();
    }

    /**
     * Find todo by ID.
     */
    public function findById(int $id): ?Todo
    {
        try {
            return $this->model::findOrFail($id);
        } catch (ModelNotFoundException) {
            Log::warning("Todo item ID {$id} not found.");

            return null;
        }
    }

    /**
     * Create a new todo.
     */
    public function create(array $data): Todo
    {
        return $this->model::create($data);
    }

    /**
     * Update a todo.
     *
     * @throws TodoNotFoundException
     */
    public function update(int $id, array $data): Todo
    {
        $todo = $this->findById($id);

        throw_unless($todo, TodoNotFoundException::class);

        $todo->update($data);

        return $todo;
    }

    /**
     * Delete a todo.
     */
    public function delete(int $id): bool
    {
        return (bool) $this->model::destroy($id);
    }
}
