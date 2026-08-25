<?php

namespace App\Providers;

use App\Contracts\Repository\UserContract;
use App\Repository\UserRepository;
use Phare\Auth\Passwords\DatabaseTokenRepository;
use Phare\Auth\Passwords\PasswordBroker;
use Phare\Hashing\BcryptHasher;
use Phare\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserContract::class, UserRepository::class);

        // Password reset broker — PDO-backed token store + the app hasher.
        $this->app->singleton('password.broker', function ($app) {
            $pdo = $app['db']->getInternalHandler();

            return new PasswordBroker(new DatabaseTokenRepository($pdo), new BcryptHasher(), 60);
        });
    }
}
