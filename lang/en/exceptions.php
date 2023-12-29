<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exception Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used in Exceptions thrown throughout the system.
    | Regardless where it is placed, a button can be listed here so it is easily
    | found in a intuitive way.
    |
    */

    'backend' => [
        'access' => [
            'roles' => [
                'already_exists' => 'That role already exists. Please choose a different name.',
                'cant_delete_admin' => 'You cannot delete the administrator role.',
                'create_error' => 'There was a problem creating this role. Please try again.',
                'delete_error' => 'There was a problem deleting this role. Please try again.',
                'has_users' => 'You cannot delete a role with associated users.',
                'needs_permission' => 'This role needs at least one permission selected.',
                'not_found' => 'That role does not exist.',
                'update_error' => 'There was a problem updating this role. Please try again.',
            ],

            'users' => [
                'already_confirmed' => 'This user is already confirmed.',
                'cant_deactivate_self' => 'You cannot deactivate yourself.',
                'cant_delete_self' => 'You cannot delete yourself.',
                'cant_restore' => 'This user cannot be restored because it was not deleted.',
                'cant_unconfirm_admin' => 'You cannot unconfirm the administrator.',
                'cant_unconfirm_self' => 'You cannot unconfirm yourself.',
                'cant_confirm' => 'There was a problem confirming this user.',
                'create_error' => 'There was a problem creating this user. Please try again.',
                'delete_error' => 'There was a problem deleting this user. Please try again.',
                'delete_first' => 'This user needs to be deleted before it can be permanently deleted.',
                'email_error' => 'That email address is taken by another user.',
                'mark_error' => 'There was a problem updating this user. Please try again.',
                'not_confirmed' => 'This user is not confirmed.',
                'not_found' => 'That user does not exist.',
                'restore_error' => 'There was a problem restoring this user. Please try again.',
                'role_needed_create' => 'You need to select at least one role.',
                'role_needed' => 'You need to select at least one role.',
                'update_error' => 'There was a problem updating this user. Please try again.',
                'update_password_error' => 'There was a problem changing this user\'s password. Please try again.',
            ],
        ],
    ],

    'frontend' => [
        'auth' => [
            'confirmation' => [
                'already_confirmed' => 'Your account is already confirmed.',
                'confirm' => 'Please confirm your account!',
                'created_confirm' => 'Your account was successfully created. We have sent you an email to confirm your account.',
                'created_pending' => 'Your account was successfully created. Once an administrator approves your account, a confirmation email will be sent.',
                'mismatch' => 'Your confirmation code does not match.',
                'not_found' => 'That confirmation code does not exist.',
                'pending' => 'Your account is currently pending approval. Once approved, a confirmation email will be sent.',
                'resend' => 'Your account is not confirmed. Please click on the confirmation link in your email, or <a href="%url%">click here</a> to resend.',
                'success' => 'Your account has been successfully confirmed!',
                'resent' => 'A new confirmation email has been sent to the file address.',
            ],

            'deactivated' => 'Your account has been deactivated.',
            'email_taken' => 'That email address is already taken.',

            'password' => [
                'change_mismatch' => 'That is not your old password.',
                'reset_problem' => 'There was a problem resetting your password. Please resend the reset email.',
            ],

            'registration_disabled' => 'Registration is currently disabled.',
        ],
    ],
];
