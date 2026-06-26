-- 管理画面 (broadcasting monitor) アクセス制御用フラグを users に追加
ALTER TABLE `users`
    ADD COLUMN `is_admin` boolean NOT NULL DEFAULT false;
