<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Exception Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used in Exceptions thrown throughout the system.
    | Regardless where it is placed, a button can be listed here so it is easily
    | found in a intuitive way.
    |
    */

    'backend' => [
        'access' => [
            'roles' => [
                'already_exists'    => 'そのロールは既に存在します。 別の名前を選んでください。',
                'cant_delete_admin' => '管理者の役割は削除できません。',
                'create_error'      => 'このロールを作成する際に問題がありました。 もう一度お試しください。',
                'delete_error'      => 'このロールを削除する際に問題がありました。 もう一度お試しください。',
                'has_users'         => 'ユーザーが関連付けられているロールは削除できません。',
                'needs_permission'  => 'このロールには少なくとも1つの権限を選択する必要があります。',
                'not_found'         => 'その役割は存在しません。',
                'update_error'      => 'このロールを更新する際に問題がありました。 もう一度お試しください。 ',
            ],

            'users' => [
                'already_confirmed'     => 'このユーザーは既に承認されています。',
                'cant_deactivate_self'  => 'あなたは自分を非アクティブに変更できないです。',
                'cant_delete_self'      => 'あなた自身を削除できません。',
                'cant_restore'          => 'このユーザーは削除されてないため、復元できません。',
                'cant_unconfirm_admin'  => '管理者の承認は取り消せません。',
                'cant_unconfirm_self'   => 'あなたは自分の承認を取り消せません。',
                'cant_confirm'          => 'このユーザーの承認中に問題が発生しました。',
                'create_error'          => 'このユーザーを作成する際に問題がありました。もう一度お試しください。',
                'delete_error'          => 'このユーザーの削除中に問題が発生しました。もう一度お試しください。',
                'delete_first'          => 'このユーザーは、永久に削除される前に削除する必要があります。',
                'email_error'           => 'そのメールアドレスは別のユーザーに属しています。',
                'mark_error'            => 'このユーザーを更新する際に問題がありました。もう一度お試しください。',
                'not_confirmed'         => 'そのユーザーは承認されていません。',
                'not_found'             => 'そのユーザーは存在しません。',
                'restore_error'         => 'このユーザーを復元する際に問題がありました。もう一度お試しください。',
                'role_needed_create'    => '少なくとも1つの役割を選択する必要があります。',
                'role_needed'           => '少なくとも1つの役割を選択する必要があります。',
                'update_error'          => 'このユーザーを更新する際に問題がありました。もう一度お試しください。',
                'update_password_error' => 'このユーザーのパスワードを変更する際に問題がありました。もう一度お試しください。',
            ],
        ],
    ],

    'frontend' => [
        'auth' => [
            'confirmation' => [
                'already_confirmed' => 'あなたのアカウントは既に確認済みです。',
                'confirm'           => 'あなたのアカウントを確認してください！',
                'created_confirm'   => 'あなたのアカウントは正常に作成されました。 あなたのアカウントを確認するための電子メールをお送りしました。',
                'created_pending'   => 'あなたのアカウントは正常に作成されました。 管理者があなたのアカウントを承認すると、確認の電子メールが送信されます。',
                'mismatch'          => 'あなたの確認コードが一致しません。',
                'not_found'         => 'その確認コードは存在しません。',
                'pending'           => 'あなたのアカウントは現在承認待ちです。 承認されると、確認の電子メールが送信されます。',
                'resend'            => 'あなたのアカウントは確認されていません。 あなたの電子メールの確認リンクをクリックするか、 <a href="%url%">ここをクリック</a>して再送信してください。',
                'success'           => 'あなたのアカウントは正常に確認されました！',
                'resent'            => '新しい確認メールがファイルのアドレスに送信されました。',
            ],

            'deactivated' => 'あなたのアカウントは無効になっています。',
            'email_taken' => 'その電子メールアドレスはすでに取得済みです。',

            'password' => [
                'change_mismatch' => '古いパスワードではありません。',
                'reset_problem'   => 'パスワードをリセットする際に問題が発生しました。 リセットメールを再送信してください。',
            ],

            'registration_disabled' => '新規登録は停止中です。',
        ],
    ],
];
