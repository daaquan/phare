-- ユーザー情報テーブル
CREATE TABLE `users`
(
    `id`                bigint unsigned NOT NULL AUTO_INCREMENT,
    `name`              varchar(100) NOT NULL DEFAULT '',
    `email`             varchar(100) NOT NULL,
    `email_verified_at` datetime              DEFAULT NULL,
    `password`          varchar(100) NOT NULL,
    `birthday`          date                  DEFAULT NULL,
    `created_at`        datetime NULL DEFAULT NULL,
    `updated_at`        datetime NULL DEFAULT NULL,
    `deleted_at`        datetime NULL DEFAULT NULL,
    UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
