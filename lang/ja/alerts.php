<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alert Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain alert messages for various scenarios
    | during CRUD operations. You are free to modify these language lines
    | according to your application's requirements.
    |
    */

    'backend' => [
        'access' => [
            'roles' => [
                'created' => 'ロールが正常に作成されました。',
                'deleted' => 'ロールが削除されました。',
                'updated' => 'ロールが正常に更新されました。',
            ],

            'users' => [
                'cant_resend_confirmation' => 'アプリケーションは現在、ユーザーを手動で承認するように設定されています。',
                'confirmation_email' => '新しい確認メールがファイルのアドレスに送信されました。',
                'confirmed' => 'ユーザーが正常に承認されました。',
                'created' => 'ユーザーが正常に作成されました。',
                'deleted' => 'ユーザーが削除されました。',
                'deleted_permanently' => 'ユーザーが完全に削除されました。',
                'restored' => 'ユーザーが正常に復元されました。',
                'updated' => 'ユーザーが正常に更新されました。',
                'updated_password' => 'ユーザーのパスワードが正常に更新されました。',
            ],
        ],
    ],

    'frontend' => [
        'contact' => [
            'sent' => 'あなたの情報は正常に送信されました。 私たちはできるだけ早く提供された電子メールに返信します。',
        ],
    ],
];
