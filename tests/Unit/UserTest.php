<?php

namespace Tests\Unit;

use App\Models\User;
use Closure;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_casts_method_returns_array(): void
    {
        $user = new User;

        $closure = Closure::bind(function () {
            return $this->casts();
        }, $user, User::class);

        $casts = $closure();

        $expected = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

        $this->assertIsArray($casts);
        $this->assertSame($expected, $casts);
    }

    public function test_get_fillable_contains_expected_fields(): void
    {
        $user = new User;

        $fillable = $user->getFillable();

        $this->assertSame([
            'name',
            'email',
            'password',
        ], $fillable);
    }

    public function test_get_hidden_contains_expected_fields(): void
    {
        $user = new User;

        $hidden = $user->getHidden();

        $this->assertSame([
            'password',
            'remember_token',
        ], $hidden);
    }
}
