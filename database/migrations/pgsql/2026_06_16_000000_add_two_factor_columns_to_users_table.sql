-- Add the two-factor (TOTP) columns to the users table
ALTER TABLE `users`
    ADD COLUMN `two_factor_secret`         text     NULL DEFAULT NULL,
    ADD COLUMN `two_factor_recovery_codes` text     NULL DEFAULT NULL,
    ADD COLUMN `two_factor_confirmed_at`   datetime NULL DEFAULT NULL;
