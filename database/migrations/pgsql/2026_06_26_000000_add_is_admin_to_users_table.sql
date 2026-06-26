-- Add a flag to users for admin access control (broadcasting monitor)
ALTER TABLE `users`
    ADD COLUMN `is_admin` boolean NOT NULL DEFAULT false;
