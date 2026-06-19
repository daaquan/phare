-- Passkeys (WebAuthn credentials) table
CREATE TABLE `passkeys`
(
    `id`            bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id`       bigint unsigned NOT NULL,
    `name`          varchar(255) NOT NULL DEFAULT '',
    `credential_id` varchar(512) NOT NULL,
    `data`          text         NOT NULL,
    `last_used_at`  datetime              DEFAULT NULL,
    `created_at`    datetime NULL DEFAULT NULL,
    `updated_at`    datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
    KEY `passkeys_user_id_index` (`user_id`),
    CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
