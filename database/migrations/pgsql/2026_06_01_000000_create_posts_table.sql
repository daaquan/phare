-- 投稿テーブル
CREATE TABLE `posts`
(
    `id`         bigint unsigned NOT NULL AUTO_INCREMENT,
    `user_id`    bigint unsigned NOT NULL,
    `title`      varchar(200) NOT NULL DEFAULT '',
    `body`       text                  DEFAULT NULL,
    `created_at` datetime NULL DEFAULT NULL,
    `updated_at` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `posts_user_id_index` (`user_id`),
    CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
