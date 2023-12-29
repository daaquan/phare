<?php

namespace App\Providers;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Flash\Session as FlashSession;
use Phalcon\Html\Escaper;
use Phalcon\Session\Adapter\Stream;
use Phalcon\Session\Manager;
use Phox\Foundation\Micro as Application;

class AppServiceProvider implements ServiceProviderInterface
{
    public function register(Application|DiInterface $app): void
    {
        $app->singleton('session', function () use ($app) {
            $files = new Stream([
                'savePath' => $app->storagePath('framework/sessions/'),
            ]);
            $session = (new Manager())->setAdapter($files);
            $session->start();

            return $session;
        });

        $app->singleton('flashSession', function () use ($app) {
            $session = $app->get('session');

            $flash = new FlashSession(new Escaper(), $session);
            $flash->setAutoescape(false);
            $flash->setCssClasses(
                [
                    'error' => 'alert alert-danger fade show',
                    'success' => 'alert alert-success fade show',
                    'notice' => 'alert alert-info fade show',
                    'warning' => 'alert alert-warning fade show',
                ]
            );

            return $flash;
        });

        $app->singleton(
            \Phalcon\Encryption\Security::class,
            function () use ($app) {
                $security = new \Phalcon\Encryption\Security();
                $security->setWorkFactor(12);
                $security->setDI($app);

                return $security;
            }
        );

        $app->singleton(
            \Phalcon\Encryption\Crypt\CryptInterface::class,
            function () use ($app) {
                $config = $app['config']->path('app');

                return (new \Phalcon\Encryption\Crypt($config->cipher))
                    ->setKey($config->key);
            }
        );

        $app->bind(
            \App\Contracts\Repository\UserContract::class,
            \App\Repository\UserRepository::class
        );
    }
}
