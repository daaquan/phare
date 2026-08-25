<?php

namespace App\Providers;

use App\Services\SqidsGenerator;
use Phare\Support\ServiceProvider;

class SqidsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('sqids', fn () => new SqidsGenerator());
    }
}
