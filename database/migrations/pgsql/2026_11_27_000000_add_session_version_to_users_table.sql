-- Bumped on every password change/reset; sessions stamped with an older value are signed out
ALTER TABLE `users`
    ADD COLUMN `session_version` integer NOT NULL DEFAULT 0;
