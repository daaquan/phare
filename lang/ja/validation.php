<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => '%attribute%を承認してください。',
    'active_url'           => '%attribute%は、有効なURLではありません。',
    'after'                => '%attribute%には、:date以降の日付を指定してください。',
    'after_or_equal'       => 'The %attribute% must be a date after or equal to :date.',
    'alpha'                => '%attribute%には、アルファベッドのみ使用できます。',
    'alpha_dash'           => "%attribute%には、英数字('A-Z','a-z','0-9')とハイフンと下線('-','_')が使用できます。",
    'alpha_num'            => "%attribute%には、英数字('A-Z','a-z','0-9')が使用できます。",
    'array'                => '%attribute%には、配列を指定してください。',
    'before'               => '%attribute%には、:date以前の日付を指定してください。',
    'before_or_equal'      => 'The %attribute% must be a date before or equal to :date.',
    'between'              => [
        'numeric' => '%attribute%には、%min%から、%max%までの数字を指定してください。',
        'file'    => '%attribute%には、%min% KBから%max% KBまでのサイズのファイルを指定してください。',
        'string'  => '%attribute%は、%min%文字から%max%文字にしてください。',
        'array'   => '%attribute%の項目は、%min%個から%max%個にしてください。',
    ],
    'boolean'              => "%attribute%には、'true'か'false'を指定してください。",
    'confirmed'            => '%attribute%と%attribute%確認が一致しません。',
    'date'                 => '%attribute%は、正しい日付ではありません。',
    'date_format'          => "%attribute%の形式は、':format'と合いません。",
    'different'            => '%attribute%と:otherには、異なるものを指定してください。',
    'digits'               => '%attribute%は、:digits桁にしてください。',
    'digits_between'       => '%attribute%は、%min%桁から%max%桁にしてください。',
    'dimensions'           => 'The %attribute% has invalid image dimensions.',
    'distinct'             => 'The %attribute% field has a duplicate value.',
    'email'                => '%attribute%は、有効なメールアドレス形式で指定してください。',
    'exists'               => '選択された%attribute%は、有効ではありません。',
    'file'                 => 'The %attribute% must be a file.',
    'filled'               => '%attribute%は必須です。',
    'image'                => '%attribute%には、画像を指定してください。',
    'in'                   => '選択された%attribute%は、有効ではありません。',
    'in_array'             => 'The %attribute% field does not exist in :other.',
    'integer'              => '%attribute%には、整数を指定してください。',
    'ip'                   => '%attribute%には、有効なIPアドレスを指定してください。',
    'ipv4'                 => 'The %attribute% must be a valid IPv4 address.',
    'ipv6'                 => 'The %attribute% must be a valid IPv6 address.',
    'json'                 => '%attribute%には、有効なJSON文字列を指定してください。',
    'max'                  => [
        'numeric' => '%attribute%には、%max%以下の数字を指定してください。',
        'file'    => '%attribute%には、%max% KB以下のファイルを指定してください。',
        'string'  => '%attribute%は、%max%文字以下にしてください。',
        'array'   => '%attribute%の項目は、%max%個以下にしてください。',
    ],
    'mimes'                => '%attribute%には、%values%タイプのファイルを指定してください。',
    'mimetypes'            => '%attribute%には、%values%タイプのファイルを指定してください。',
    'min'                  => [
        'numeric' => '%attribute%には、%min%以上の数字を指定してください。',
        'file'    => '%attribute%には、%min% KB以上のファイルを指定してください。',
        'string'  => '%attribute%は、%min%文字以上にしてください。',
        'array'   => '%attribute%の項目は、%max%個以上にしてください。',
    ],
    'not_in'               => '選択された%attribute%は、有効ではありません。',
    'numeric'              => '%attribute%には、数字を指定してください。',
    'present'              => 'The %attribute% field must be present.',
    'regex'                => '%attribute%には、有効な正規表現を指定してください。',
    'required'             => '%attribute%を入力してください',
    'required_if'          => ':otherが:valueの場合、%attribute%を指定してください。',
    'required_unless'      => ':otherが:value以外の場合、%attribute%を指定してください。',
    'required_with'        => '%values%が指定されている場合、%attribute%も指定してください。',
    'required_with_all'    => '%values%が全て指定されている場合、%attribute%も指定してください。',
    'required_without'     => '%values%が指定されていない場合、%attribute%を指定してください。',
    'required_without_all' => '%values%が全て指定されていない場合、%attribute%を指定してください。',
    'same'                 => '%attribute%と:otherが一致しません。',
    'size'                 => [
        'numeric' => '%attribute%には、:sizeを指定してください。',
        'file'    => '%attribute%には、:size KBのファイルを指定してください。',
        'string'  => '%attribute%は、:size文字にしてください。',
        'array'   => '%attribute%の項目は、:size個にしてください。',
    ],
    'string'               => '%attribute%には、文字を指定してください。',
    'timezone'             => '%attribute%には、有効なタイムゾーンを指定してください。',
    'unique'               => '指定の%attribute%は既に使用されています。',
    'uploaded'             => 'The %attribute% failed to upload.',
    'url'                  => '%attribute%は、有効なURL形式で指定してください。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [

        'backend' => [
            'access' => [
                'permissions' => [
                    'associated_roles' => 'Associated Roles',
                    'dependencies'     => 'Dependencies',
                    'display_name'     => 'Display Name',
                    'group'            => 'Group',
                    'group_sort'       => 'Group Sort',

                    'groups' => [
                        'name' => 'Group Name',
                    ],

                    'name'   => 'Name',
                    'system' => 'System?',
                ],

                'roles' => [
                    'associated_permissions' => 'Associated Permissions',
                    'name'                   => 'Name',
                    'sort'                   => 'Sort',
                ],

                'users' => [
                    'active'                  => 'Active',
                    'associated_roles'        => 'Associated Roles',
                    'confirmed'               => 'Confirmed',
                    'email'                   => 'E-mail Address',
                    'name'                    => 'Name',
                    'other_permissions'       => 'Other Permissions',
                    'password'                => 'Password',
                    'password_confirmation'   => 'Password Confirmation',
                    'send_confirmation_email' => 'Send Confirmation E-mail',
                ],
            ],
        ],

        'frontend' => [
            'email'                     => 'メールアドレス',
            'name'                      => 'ユーザ名',
            'password'                  => 'パスワード',
            'password_confirmation'     => 'パスワード（確認）',
            'phone'                     => '電話番号',
            'message'                   => 'メッセージ',
            'old_password'              => '旧パスワード',
            'new_password'              => '新パスワード',
            'new_password_confirmation' => '新パスワード（確認）',
        ],
    ],
];
