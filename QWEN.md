# Phare Framework - Codebase Documentation

## Overview

Phare Framework is a scalable, high-performance PHP game framework built on top of the Phalcon framework. It combines modern PHP development practices with Phalcon's performance to help build robust and efficient game application backends. The framework supports both API development and web applications.

## Key Features

1. **Phalcon-based**: Uses Phalcon (v5.4~), a high-performance C extension, as its core.
2. **Modular System**: Applications can be divided and managed by modules (API, Web, etc.).
3. **Routing**:
   - Intuitive route definitions using controller attributes (`#[Route]`, `#[RoutePrefix]`)
   - File-based routing definitions also supported
   - Route caching for performance improvement
4. **Database Access**:
   - Eloquent-like ORM with intuitive and powerful features
   - Supports multiple database systems (MySQL, PostgreSQL, SQLite)
   - Database sharding support for large-scale data processing
   - Convenient features like timestamps (`created_at`, `updated_at`) and soft deletes
5. **Template Engine**:
   - Custom Blade template engine based on BladeOne
   - Custom Blade directives for HTML generation and CSS framework components
   - Support for Phalcon's standard Volt template engine
6. **Authentication**: Easy-to-build session-based user authentication system
7. **Session Management**: Flexible session management with file and Redis (including cluster configuration) backends
8. **Caching System**: Cache functionality supporting multiple drivers (file, Redis, APC)
9. **Queueing System**: Integrated queueing system for asynchronous task processing using Beanstalkd
10. **Error Handling and Logging**:
    - Robust exception handling and debugging features
    - Configurable multi-channel logging system (file, Syslog, stderr, etc.)
11. **Testing Suite**:
    - Test environment supporting PHPUnit and Pest PHP
    - Convenient assertion methods for HTTP request simulation and response validation
12. **Development Tools**:
    - Integrated PSR-12 compliant code style auto-correction with Laravel Pint
    - Static code analysis support with PHP Insights
13. **Utilities**:
    - Extended date/time manipulation class (`Phare\Support\Chronos`)
    - ID encoding/decoding functionality using Sqids
    - Helper classes for arrays, collections, and string operations (`Arr`, `Collection`, `Str`)
    - Secure environment variable management using Symfony DotEnv component
14. **Service Container and Facade**:
    - Robust DI container for dependency management and resolution
    - Facade pattern for easy access to major services
15. **Helper Functions**: Numerous global helper functions (`config()`, `env()`, `app()`, `response()`, `request()`, `route()`, `session()`, `now()`) to improve development efficiency

## System Requirements

- PHP ^8.2
- Required PHP extensions:
  - `ext-mbstring`
  - `ext-openssl`
  - `ext-intl`
  - `ext-pdo`
  - `ext-gmp` (recommended for Sqids)
  - `ext-bcmath` (recommended for Sqids)
  - `ext-phalcon` (~5.4)
  - `ext-sqids` (recommended for ID generator)
  - `ext-chronos` (recommended for Chronos DateTime library)
- Recommended PHP extensions:
  - `ext-redis` (for Redis cache/session usage)
  - `ext-msgpack` (for msgpack serializer usage)

## Project Structure

```
phare/
├── app/                    # Application source code
│   ├── Console/           # Console commands
│   ├── Contracts/          # Interfaces for services
│   ├── Exceptions/         # Exception handlers
│   ├── Http/               # HTTP layer (controllers, middleware, requests)
│   ├── Models/             # Database models
│   ├── Providers/          # Service providers
│   ├── Repository/         # Data access layer
│   └── helpers.php         # Global helper functions
├── bootstrap/             # Application bootstrap files
├── config/                # Configuration files
├── database/              # Database migrations
├── docker/                # Docker configuration
├── lang/                  # Language files
├── public/                # Publicly accessible files
├── resources/             # Views, assets, and other resources
├── routes/                # Route definitions
├── storage/               # Storage for logs, cache, etc.
├── tests/                 # Test files
├── vendor/                # Composer dependencies
├── .env.example           # Environment file example
├── composer.json          # Composer configuration
├── README.md              # Project documentation
└── ...
```

## Core Components

### 1. Routing

Routes are defined in files under the `routes/` directory or using controller attributes.

Example using attributes:
```php
#[RoutePrefix('/users')]
class UserController
{
    #[Route('/', methods: ['GET'])]
    public function index(): array
    {
        return User::all()->toArray();
    }
}
```

### 2. Models

Database interactions are handled through Eloquent-like models.

Example User model:
```php
class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasTimestamps;

    protected ?string $table = 'users';
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];
    protected array $casts = ['email_verified_at' => 'datetime'];
}
```

### 3. Authentication

The framework provides a session-based authentication system with controllers for login/logout operations.

### 4. Views (Blade)

HTML rendering uses the Blade template engine.

Example Blade template:
```blade
@extends('layouts.app')

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
@endsection
```

### 5. Repositories

The framework implements a repository pattern for data access, providing a clean separation between business logic and data access.

Example UserRepository:
```php
class UserRepository implements UserContract
{
    public function getUserById(int $id): ?User
    {
        return User::findFirstById($id);
    }

    public function createUser(array $data): User
    {
        $user = (new User())->fill($data);
        if ($user->validationHasFailed() || !$user->create()) {
            throw new ModelException(implode("\n", $user->getMessages()));
        }
        return $user;
    }
}
```

## Testing

The framework supports testing with both PHPUnit and Pest PHP.

To run tests:
```bash
# PHPUnit tests
./vendor/bin/phpunit

# Pest PHP tests
./vendor/bin/pest
```

## Code Quality

### Code Style

Laravel Pint is used for code style consistency:
```bash
./vendor/bin/pint
```

### Static Analysis

PHP Insights is configured for static code analysis:
```bash
./vendor/bin/phpinsights
```

## Installation

1. Clone the repository:
   ```bash
   git clone <repository_url> phare-framework
   cd phare-framework
   ```

2. Install Composer dependencies:
   ```bash
   composer install
   ```

3. Configure environment:
   - Copy `.env.example` to `.env`
   - Set database connection info and application key (`APP_KEY`) in `.env`

4. Ensure write permissions for `storage` and `bootstrap/cache` directories

## Usage

The framework follows a typical MVC-like architecture:

1. Application bootstrapping through `bootstrap/app.php`
2. Route definitions in `routes/` directory or controller attributes
3. Database interactions through Eloquent-like models
4. HTML rendering with Blade templates
5. Testing with PHPUnit or Pest PHP

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Create a pull request