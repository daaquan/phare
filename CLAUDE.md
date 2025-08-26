# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Phare Framework is a scalable, high-performance PHP game framework built on top of Phalcon (v5.4+). It combines Laravel-like syntax with Phalcon's C-extension performance for building robust game application backends and APIs.

## Architecture

### Core Components
- **Phalcon Foundation**: Uses Phalcon's high-performance C extension as the core
- **Eloquent-like ORM**: Database models with Laravel-style syntax
- **Blade Templates**: Custom Blade template engine based on BladeOne
- **Artisan Console**: Command-line interface for development tasks
- **Repository Pattern**: Data access layer separation
- **Service Providers**: Dependency injection and service registration
- **Attribute-based Routing**: Modern PHP 8+ attribute routing alongside file-based routes

### Directory Structure
- `app/` - Application source code (MVC pattern)
  - `Console/` - Artisan commands and console kernel
  - `Http/Controllers/` - API and Web controllers
  - `Models/` - Eloquent-like database models
  - `Repository/` - Data access layer
  - `Providers/` - Service providers for DI container
- `config/` - Configuration files for database, auth, etc.
- `routes/` - Route definitions (api.php, callbacks.php)
- `resources/` - Views (Blade templates) and frontend assets
- `database/migrations/` - Database migration files
- `tests/` - PHPUnit and Pest PHP test files

## Common Development Commands

### Testing
```bash
# Run PHPUnit tests
./vendor/bin/phpunit

# Run Pest PHP tests  
./vendor/bin/pest
```

### Code Quality
```bash
# Format code with Laravel Pint (PSR-12 compliant)
./vendor/bin/pint

# Run static analysis with PHP Insights
./vendor/bin/phpinsights
```

### Frontend Build
```bash
# Development build
npm run dev

# Watch for changes
npm run watch

# Production build  
npm run prod
```

### Artisan Console
```bash
# Run console commands via PHP artisan script
php artisan <command>

# Available commands include:
# - about: Application information
# - key:generate: Generate application key
# - migrate: Run database migrations
# - make:migration: Create new migration
# - queue:work: Process queue jobs
```

## Key Framework Features

### Routing
- Uses PHP 8+ attributes for route definitions: `#[Route]`, `#[RoutePrefix]`
- File-based routing in `routes/` directory
- Route caching for performance

### Models
- Eloquent-like ORM with `fillable`, `hidden`, `casts` properties
- Timestamps support (`created_at`, `updated_at`) 
- Soft deletes capability
- Database sharding support

### Authentication
- Session-based authentication system
- `Authenticatable` trait and contract implementation
- Login controllers for both API and Web

### Templates
- Blade template engine with custom directives
- Layout inheritance with `@extends`, `@section`
- Component-based view architecture

### Testing
- Both PHPUnit and Pest PHP support configured
- Feature and Unit test separation
- HTTP testing capabilities for controllers

## Development Notes

- PHP 8.2+ required with Phalcon extension
- Uses Laravel Mix for frontend asset compilation (Sass, JS)
- Docker configuration available in `docker/` directory
- Multi-language support (English, Japanese) in `lang/` directory
- Follows PSR-12 coding standards enforced by Laravel Pint