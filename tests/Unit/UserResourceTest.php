<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class UserResourceTest extends TestCase
{
    public function test_to_array_exposes_expected_attributes(): void
    {
        $stamp = new class
        {
            public function toDateTimeString(): string
            {
                return '2026-01-02 03:04:05';
            }
        };

        // Plain public properties bypass Eloquent date casting (no DB needed).
        $user = new class extends User
        {
            public $created_at;

            public $updated_at;
        };
        $user->id = 7;
        $user->name = 'Jane Doe';
        $user->email = 'jane@example.com';
        $user->created_at = $stamp;
        $user->updated_at = $stamp;

        $resource = new UserResource($user);

        $result = $resource->toArray(new Request);

        $this->assertSame(User::class, $result['type']);
        $this->assertSame(7, $result['id']);
        $this->assertSame([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'created_at' => '2026-01-02 03:04:05',
            'updated_at' => '2026-01-02 03:04:05',
        ], $result['attributes']);
    }

    public function test_to_array_handles_null_timestamps(): void
    {
        $user = new User;
        $user->id = 8;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        $resource = new UserResource($user);

        $result = $resource->toArray(new Request);

        $this->assertNull($result['attributes']['created_at']);
        $this->assertNull($result['attributes']['updated_at']);
        $this->assertArrayNotHasKey('password', $result['attributes']);
    }
}
