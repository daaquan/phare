<?php

namespace App\Providers;

use App\Contracts\Repository\UserContract;
use App\Repository\UserRepository;
use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phare\Auth\Passwords\DatabaseTokenRepository;
use Phare\Auth\Passwords\PasswordBroker;
use Phare\Foundation\Micro as Application;
use Phare\Hashing\BcryptHasher;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        $app->bind(UserContract::class,
            UserRepository::class);

        // Password reset broker — PDO-backed token store + the app hasher.
        $app->singleton('password.broker', function ($app) {
            $pdo = $app['db']->getInternalHandler();

            return new PasswordBroker(new DatabaseTokenRepository($pdo), new BcryptHasher(), 60);
        });
    }
}
