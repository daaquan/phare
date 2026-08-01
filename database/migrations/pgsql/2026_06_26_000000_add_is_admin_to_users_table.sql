-- Add the flag gating admin screen (broadcasting monitor) access to users
ALTER TABLE `users`
    ADD COLUMN `is_admin` boolean NOT NULL DEFAULT false;
