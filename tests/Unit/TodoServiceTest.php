<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Todo;
use App\Repositories\TodoRepository;
use App\Services\TodoService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

class TodoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_get_all_todos_returns_collection(): void
    {
        $coll = new Collection([]);

        $repo = Mockery::mock(TodoRepository::class);
        $repo->shouldReceive('getAll')->once()->andReturn($coll);

        $service = new TodoService($repo);

        $this->assertSame($coll, $service->getAllTodos());
    }

    public function test_get_todo_by_id_returns_todo(): void
    {
        $todo = new Todo;

        $repo = Mockery::mock(TodoRepository::class);
        $repo->shouldReceive('findById')->once()->with(5)
            ->andReturn($todo);

        $service = new TodoService($repo);

        $this->assertSame($todo, $service->getTodoById(5));
    }

    public function test_create_todo_calls_repository_and_returns_todo(): void
    {
        $data = ['title' => 'x'];
        $todo = new Todo;

        $repo = Mockery::mock(TodoRepository::class);
        $repo->shouldReceive('create')->once()->with($data)
            ->andReturn($todo);

        $service = new TodoService($repo);

        $this->assertSame($todo, $service->createTodo($data));
    }

    public function test_update_todo_calls_repository_and_returns_todo(): void
    {
        $id = 9;
        $data = ['title' => 'y'];
        $todo = new Todo;

        $repo = Mockery::mock(TodoRepository::class);
        $repo->shouldReceive('update')->once()->with($id, $data)
            ->andReturn($todo);

        $service = new TodoService($repo);

        $this->assertSame($todo, $service->updateTodo($id, $data));
    }

    public function test_delete_todo_calls_repository_and_returns_bool(): void
    {
        $repo = Mockery::mock(TodoRepository::class);
        $repo->shouldReceive('delete')->once()->with(3)
            ->andReturnTrue();

        $service = new TodoService($repo);

        $this->assertTrue($service->deleteTodo(3));
    }
}
