<?php

use Phare\Contracts\Auth\CanResetPassword;

$requiresDatabase = !in_array('sqlite', PDO::getAvailableDrivers(), true);

function resettableUser(string $email): CanResetPassword
{
    return new class($email) implements CanResetPassword
    {
        public function __construct(private string $email) {}

        public function getEmailForPasswordReset(): string
        {
            return $this->email;
        }
    };
}

test('a freshly created token validates', function () {
    $broker = app('password.broker');
    $token = $broker->createToken(resettableUser('a@example.com'));

    expect($broker->validateToken('a@example.com', $token))->toBeTrue();
})->skip($requiresDatabase, 'sqlite PDO driver unavailable');

test('a wrong token does not validate', function () {
    $broker = app('password.broker');
    $broker->createToken(resettableUser('b@example.com'));

    expect($broker->validateToken('b@example.com', 'not-the-token'))->toBeFalse();
})->skip($requiresDatabase, 'sqlite PDO driver unavailable');

test('a deleted token no longer validates', function () {
    $broker = app('password.broker');
    $token = $broker->createToken(resettableUser('c@example.com'));
    $broker->deleteToken('c@example.com');

    expect($broker->validateToken('c@example.com', $token))->toBeFalse();
})->skip($requiresDatabase, 'sqlite PDO driver unavailable');
