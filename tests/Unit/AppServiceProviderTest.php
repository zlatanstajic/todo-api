<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class AppServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        ResetPassword::$createUrlCallback = null;
        Container::setInstance(null);
    }

    public function test_boot_registers_reset_password_url_builder(): void
    {
        $container = new Container;
        Container::setInstance($container);
        $container->singleton('config', fn (): Repository => new Repository([
            'app' => ['frontend_url' => 'https://spa.test'],
        ]));

        $provider = new AppServiceProvider($container);

        $provider->register();
        $provider->boot();

        $this->assertNotNull(ResetPassword::$createUrlCallback);

        $user = new User;
        $user->email = 'jane@example.com';

        $url = call_user_func(ResetPassword::$createUrlCallback, $user, 'the-token');

        $this->assertSame(
            'https://spa.test/reset-password?token=the-token&email=jane@example.com',
            $url
        );
    }
}
