<?php

use App\Models\User;
use Phare\Support\Facades\Broadcast;

/*
 * ブロードキャストのチャンネル認可。
 * private / presence チャンネル購読時に POST /broadcasting/auth から評価される。
 * コールバックは (認証ユーザー, ...チャンネルパラメータ) を受け取り、
 * - false / null  → 認可拒否
 * - true          → 許可 (private)
 * - 配列          → 許可 + presence メンバー情報
 * を返す。
 */

// 本人のみが購読できる private チャンネル: private-App.User.{id}
Broadcast::channel('App.User.{id}', function (?User $user, $id) {
    return $user !== null && (int)$user->id === (int)$id;
});

// 監視デモ用の presence チャンネル: presence-monitor
// ログイン済みなら誰でも参加でき、メンバー情報を返す。
Broadcast::channel('monitor', function (?User $user) {
    if ($user === null) {
        return false;
    }

    return ['id' => $user->id, 'name' => $user->name];
});
