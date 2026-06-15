-- Password reset tokens table
CREATE TABLE `password_reset_tokens`
(
    `email`      varchar(100) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
