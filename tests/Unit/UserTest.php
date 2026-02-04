<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function test_casts_method_returns_array(): void
    {
        $user = new User;

        $closure = $user->casts(...);

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
