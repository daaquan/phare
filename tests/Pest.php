<?php

use Phare\Database\Schema\Blueprint;
use Phare\Database\Schema\SchemaBuilder;

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

uses(Tests\TestCase::class)
    ->beforeEach(function () {
        $this->setUpApplication();

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

function something()
{
}

/**
 * Build a fresh sqlite schema for tests (RefreshDatabase-style). The app's
 * migrations are MySQL .sql files, so the portable Schema builder is used
 * to recreate the same tables on the in-test sqlite connection.
 */
function migrateTestSchema($app): void
{
    $connection = $app->make('db');
    $schema = new SchemaBuilder($connection);

    foreach (['posts', 'users', 'password_reset_tokens'] as $table) {
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
}
