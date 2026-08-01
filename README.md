# Phare Framework

A scalable, full-featured PHP game framework built on top of the Phalcon framework.

**Keywords:** phalcon, framework

## Overview

Phare Framework combines modern PHP development practices with Phalcon's performance to help you build robust, efficient backends for game applications. Its design covers a wide range of needs, from API development to full web applications.

## Features

Phare Framework ships a rich feature set aimed at rapid application development.

* **Built on Phalcon**: Phalcon (v5.4+), a high-performance C extension, is used as the core.
* **Module system**: Applications can be split into and managed as modules such as API and Web.
* **Routing**:
    * Intuitive route definitions through controller attributes (`#[Route]`, `#[RoutePrefix]`).
    * Traditional file-based route definitions are supported as well.
    * Route caching is available for better performance.
* **Database access**:
    * An intuitive, powerful Eloquent-like ORM.
    * Support for multiple database systems including MySQL, PostgreSQL and SQLite.
    * Database sharding support, so large data volumes can be designed for.
    * Conveniences such as timestamps (`created_at`, `updated_at`) and soft deletes.
* **Template engine**:
    * A custom Blade template engine based on BladeOne, for simple and powerful views.
    * Custom Blade directives that make HTML generation easier (form elements, CSS framework components and so on).
    * Phalcon's standard Volt template engine is also available.
* **Authentication**: Session-based user authentication is straightforward to set up.
* **Session management**: Flexible session handling backed by files or Redis (including clustered setups).
* **Cache system**: Caching with support for multiple drivers such as file, Redis and APC.
* **Queueing system**: An integrated queue for asynchronous task processing on top of Beanstalkd.
* **Error handling and logging**:
    * Robust exception handling plus debugging facilities that surface detail during development.
    * A configurable multi-channel logging system (file, syslog, stderr and more).
* **Capable test suite**:
    * A test environment for both PHPUnit and Pest PHP, making unit and feature tests easy to write.
    * Convenient assertion methods for simulating HTTP requests and inspecting responses.
* **Development tooling**:
    * Laravel Pint is integrated for automatic PSR-12 compliant code style fixes.
    * PHPStan static analysis is supported, helping keep code quality up.
* **Useful utilities**:
    * A date/time class extending Chronos (`Phare\Support\Chronos`).
    * ID encoding/decoding through Sqids.
    * A rich set of helper classes for arrays, collections and strings (`Arr`, `Collection`, `Str`).
    * Safe environment variable handling via the Symfony DotEnv component.
* **Service container and facades**:
    * Dependency management and resolution through a solid DI container.
    * The facade pattern for easy access to the main services.
* **Helper functions**: Many global helpers that speed development up, including `config()`, `env()`, `app()`, `response()`, `request()`, `route()`, `session()` and `now()`.

## System requirements

* PHP ^8.2
* Required PHP extensions:
    * `ext-mbstring`
    * `ext-openssl`
    * `ext-intl`
    * `ext-pdo`
    * `ext-gmp` (recommended for Sqids)
    * `ext-bcmath` (recommended for Sqids)
    * `ext-phalcon` (~5.4)
    * `ext-sqids` (recommended, for the ID generator)
    * `ext-chronos` (recommended, for the Chronos DateTime library)
* Recommended PHP extensions:
    * `ext-redis` (when using the Redis cache/session)
    * `ext-msgpack` (when using the msgpack serializer)

## Installation

1.  **Clone (or download) the repository**:
    ```bash
    git clone <repository_url> phare-framework
    cd phare-framework
    ```
2.  **Install the Composer dependencies**:
    ```bash
    composer install
    ```
3.  **Configure the environment**:
    * Copy `.env.example` to create a `.env` file.
    * Fill in the database connection details, the application key (`APP_KEY`) and so on. `APP_KEY` is normally a random 32-character string.
        ```bash
        # e.g. generating APP_KEY (use `php artisan key:generate` if available)
        # openssl rand -base64 32
        ```
4.  **Directory permissions**:
    Make sure the `storage` and `bootstrap/cache` directories are writable.

## Basic usage (concepts)

Phare Framework follows a conventional MVC-like architecture.

### 1. Booting the application

The framework creates the application instance through `bootstrap/app.php` and loads the required service providers.

### 2. Routing

Routes are defined either in files under `routes/` (for example `api.php`, `web.php`) or through controller attributes.

**Example: attribute-based routing (`app/Http/Controllers/Api/ExampleController.php`)**

```php
<?php

namespace App\Http\Controllers\Api;

use Phare\Attributes\Route;
use Phare\Attributes\RoutePrefix;
use Phare\Http\Request; // Phare's Request class
use App\Models\User; // your own User model

#[RoutePrefix('/users')]
class UserController
{
    #[Route('/', methods: ['GET'])]
    public function index(): array
    {
        return User::all()->toArray();
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): array
    {
        $user = User::firstOrFail($id);
        return $user->toArray();
    }

    #[Route('/', methods: ['POST'])]
    public function store(Request $request): array
    {
        // With validation (where Phare\Http\Request provides it)
        // $validatedData = $request->validate([
        // 'name' => 'required|string|max:255',
        // 'email' => 'required|email|unique:users',
        // 'password' => 'required|min:8',
        // ]);
        // $user = User::create($validatedData);

        // The simple version
        $user = new User();
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->password = bcrypt($request->get('password')); // assumes a bcrypt helper
        $user->save();

        return ['message' => 'User created successfully', 'user_id' => $user->id];
    }
}
```

### 3. Models

Database work goes through Eloquent-like models.

**Example: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Phare\Eloquent\Model;
use Phare\Auth\Authenticatable; // Phare's Authenticatable trait
use Phare\Contracts\Auth\Authenticatable as AuthenticatableContract; // Phare's contract
use Phare\Eloquent\Concerns\HasTimestamps;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasTimestamps; // manages created_at and updated_at

    protected ?string $connection = 'mysql'; // a connection name from config/database.php
    protected ?string $table = 'users';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'email',
        'password',
        // other fillable columns
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

### 4. Views (Blade)

HTML is rendered with the Blade template engine.

**Example: `resources/views/users/index.blade.php`**

```blade
@extends('layouts.app') {{-- extends layouts/app.blade.php --}}

@section('title', 'User List')

@section('content')
    <h1>User List</h1>
    @if(count($users) > 0)
        <ul>
            @foreach($users as $user)
                <li>{{ $user->name }} ({{ $user->email }})</li>
            @endforeach
        </ul>
    @else
        <p>No users found.</p>
    @endif

    {{-- Using the custom HTML helpers (where Phare\View\Tags\BladeHtml provides them) --}}
    {{-- @button(type="button" class="btn-primary" text="Add New User" href=@route('users.create')) --}}
@endsection
```

## Testing

Phare Framework supports testing with both PHPUnit and Pest PHP.

* **Run the PHPUnit tests**:
    ```bash
    ./vendor/bin/phpunit
    ```
* **Run the Pest PHP tests**:
    ```bash
    ./vendor/bin/pest
    ```

Test cases live in the `tests/` directory. Unit tests conventionally go in `tests/Unit` and feature tests in `tests/Feature` (or wherever the project structure puts them).

## Code style

This project uses Laravel Pint to keep the code style consistent. Running it before committing is recommended.

```bash
./vendor/bin/pint
```

## Static analysis

Static analysis runs through PHPStan (level 5, configured in `phpstan.neon.dist`).

```bash
./vendor/bin/phpstan analyse
```

## Contributing

Contributions are welcome. Bug reports, feature suggestions and pull requests all belong in the Issues and Pull Requests sections of the GitHub repository.

1.  Fork the repository.
2.  Create a feature branch (`git checkout -b feature/AmazingFeature`).
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4.  Push the branch (`git push origin feature/AmazingFeature`).
5.  Open a pull request.
