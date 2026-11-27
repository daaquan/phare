<?php

use App\Models\User;
use App\Repository\UserRepository;
use App\Support\Auth\AuthSession;
use Phare\Database\Schema\Blueprint;
use Phare\Database\Schema\SchemaBuilder;
use Phare\Security\Csrf;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)
    ->beforeEach(function () {
        // MakesHttpRequests writes request headers into $_SERVER and never
        // clears them; drop them so one test's headers do not leak into the next.
        foreach (array_keys($_SERVER) as $key) {
            if (str_starts_with((string)$key, 'HTTP_') || in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                unset($_SERVER[$key]);
            }
        }

        $this->setUpApplication();

        // Web writes need the session CSRF token; tests that exercise CSRF drop it.
        $_SERVER['HTTP_X_CSRF_TOKEN'] = app(Csrf::class)->getToken();

        // Rate-limit buckets must not carry over when a persistent cache is used.
        app('cache')->flush();

        if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            migrateTestSchema($this->app);
        }

        $this->post('/api/auth/logout');
    })
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeExactlyOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something() {}

/**
 * Build a fresh sqlite schema for tests (RefreshDatabase-style). The app's
 * migrations are MySQL .sql files, so the portable Schema builder is used
 * to recreate the same tables on the in-test sqlite connection.
 */
function migrateTestSchema($app): void
{
    $connection = $app->make('db');

    // This drops and recreates tables, so it must only ever reach the sqlite
    // test database. A test run that boots with a non-testing config (e.g. a
    // stale/shared config cache) would otherwise wipe the real database.
    if ($connection->getType() !== 'sqlite') {
        throw new RuntimeException(
            'Refusing to build the test schema on a "' . $connection->getType() . '" connection.'
        );
    }

    $schema = new SchemaBuilder($connection);

    foreach (['passkeys', 'posts', 'users', 'password_reset_tokens'] as $table) {
        if ($schema->hasTable($table)) {
            $connection->execute('DROP TABLE ' . $table);
        }
    }

    $schema->create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->date('birthday')->nullable();
        $table->text('two_factor_secret')->nullable();
        $table->text('two_factor_recovery_codes')->nullable();
        $table->timestamp('two_factor_confirmed_at')->nullable();
        $table->boolean('is_admin')->default(false);
        $table->integer('session_version')->default(0);
        $table->timestamps();
    });

    $schema->create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id');
        $table->string('title');
        $table->text('body')->nullable();
        $table->timestamps();
    });

    $schema->create('password_reset_tokens', function (Blueprint $table) {
        $table->string('email')->primary();
        $table->string('token');
        $table->timestamp('created_at')->nullable();
    });

    $schema->create('passkeys', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->index();
        $table->string('name')->default('');
        $table->string('credential_id', 512)->unique();
        $table->text('data');
        $table->timestamp('last_used_at')->nullable();
        $table->timestamps();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
}

/**
 * Persist a verified user (password "secret123") on the sqlite test database.
 *
 * @param array<string, mixed> $attributes
 */
function createTestUser(array $attributes = []): User
{
    $created = (new UserRepository())->createUser(array_merge([
        'name' => 'Test User',
        'email' => 'user' . bin2hex(random_bytes(4)) . '@example.com',
        'password' => 'secret123',
        'email_verified_at' => '2026-01-01 00:00:00',
    ], $attributes));

    return User::findFirst((int)$created['id']);
}

/**
 * Sign the user in for the following requests (the PHP session outlives the
 * per-request application rebuild in MakesHttpRequests).
 */
function signIn(User $user): User
{
    // The process-wide session also carries state from earlier tests.
    foreach (['auth.password_confirmed_at', 'url.intended', 'login.id', 'passkey.creation', 'errors'] as $key) {
        app('session')->remove($key);
    }
    AuthSession::login($user);

    return $user;
}

function freshUser(int $id): User
{
    return User::findFirst($id);
}
