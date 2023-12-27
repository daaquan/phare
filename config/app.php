<?php

return [

    /*
    |--------------------------------------------------------------------------
    | アプリケーション名
    |--------------------------------------------------------------------------
    |
    | アプリケーションを識別する名前を指定します。
    | 通知やその他の場所に配置する必要がある場合に使用されます。
    |
    */

    'name' => env('APP_NAME', 'App'),

    /*
    |--------------------------------------------------------------------------
    | 稼働環境
    |--------------------------------------------------------------------------
    |
    | 環境変数に設定することで、アプリケーションの環境を指定できます。
    | さまざまなサービスをどのように設定するかを決定する場合があります。
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | 起動モジュール名
    |--------------------------------------------------------------------------
    |
    | 環境変数でモジュール名を指定します。複数のモジュールを併用し、同じ環境で動作させることができます。
    | 例えば、APIとGmTool(Web)の2つのモジュールを同じnginxで動作させる場合、.envは .env.api と
    | .env.gmtool の2つを用意する必要があり、configやrouteのキャッシュも別々に生成されます。
    |
    */

    'module' => env('APP_MODULE', 'api'),

    'coexistence' => env('APP_COEXISTENCE', false),

    /*
    |--------------------------------------------------------------------------
    | デバッグモード
    |--------------------------------------------------------------------------
    |
    | 有効にすると、エラーの詳細が表示されます。
    | 本番環境では無効にすることをお勧めします。
    |
    */

    'debug' => (bool)env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | アプリケーションURL
    |--------------------------------------------------------------------------
    |
    | コンソールでArtisanコマンドを実行するときに、URLを生成するために使用されます。
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | タイムゾーン
    |--------------------------------------------------------------------------
    |
    | PHPの日付と日時関数に使用されます。
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | ロケール
    |--------------------------------------------------------------------------
    |
    | 翻訳サービスプロバイダで使用されるデフォルトのロケールを決定します。
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | フォールバックロケール
    |--------------------------------------------------------------------------
    |
    | 現在のロケールが利用できない場合に使用するロケールを決定します。
    |
    */

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | フェイカーロケール
    |--------------------------------------------------------------------------
    |
    | Faker PHPライブラリが、データベースシードの生成時に使用するロケールを決定します。
    | 例えば、電話番号や住所情報などのローカライズされたデータを取得するために使用されます。
    |
    */

    'faker_locale' => 'ja_JP',

    /*
    |--------------------------------------------------------------------------
    | 暗号化キー
    |--------------------------------------------------------------------------
    |
    | このキーは暗号化サービスによって使用され、バイナリーまたはランダムな文字列に設定します。
    | そうしないと、これらの暗号化された文字列は安全ではなくなります。
    | アプリケーションをデプロイする前に、必ず設定してください！
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Phalcon設定
    |--------------------------------------------------------------------------
    |
    | Phalconフレームワークのデフォルト設定を変更することができます。
    |
    */

    'phalcon' => [
        // https://docs.phalcon.io/5.0/ja-jp/db-models
        'orm.enable_implicit_joins' => false, // モデル間の関連を使用して、暗黙の結合を有効にするかどうか
        'orm.exception_on_failed_save' => true, // モデルの保存に失敗した場合に例外をスローするかどうか
        'orm.force_casting' => false, // データベースから取得した値をキャストするかどうか
        'orm.ignore_unknown_columns' => true, // モデルに定義されていないカラムを無視するかどうか
        'orm.not_null_validations' => true, // モデルのプロパティがNOT NULLである場合に、NULLを許可するかどうか
        'orm.resultset_prefetch_records' => '0', // プリフェッチするレコード数
        'orm.update_snapshot_on_save' => true, // モデルのスナップショットを更新するかどうか
        'orm.virtual_foreign_keys' => false, // 仮想外部キーを有効にするかどうか
        'warning.enable' => false, // ワーニングを有効にする
    ],

    /*
    |--------------------------------------------------------------------------
    | サービスプロバイダー
    |--------------------------------------------------------------------------
    |
    | アプリケーション起動時に自動的にロードされるサービスプロバイダーをここに記載します。
    | アプリケーションの機能を拡張するために、独自のサービスをこの配列に追加してください。
    |
    */

    'providers' => [
        \Phox\Providers\ConfigProvider::class,
        \Phox\Providers\LogServiceProvider::class,
        \Phox\Providers\EventsManagerProvider::class,
        \Phox\Providers\ErrorHandlerProvider::class,
        \Phox\Providers\DebugLoggerProvider::class,
        \Phox\Providers\UrlProvider::class,
        \Phox\Providers\DispatcherProvider::class,
        \Phox\Providers\ViewProvider::class,
        \Phox\Providers\EncrypterProvider::class,
        \Phox\Providers\SecurityProvider::class,
        \Phox\Providers\RandomProvider::class,
        \Phox\Providers\SqidsProvider::class,
        \Phox\Providers\RouteServiceProvider::class,
        \Phox\Providers\RequestProvider::class,
        \Phox\Providers\ResponseProvider::class,
        \Phox\Providers\SessionProvider::class,
        \Phox\Providers\AuthProvider::class,
        \Phox\Providers\ModelProvider::class,
        \Phox\Providers\DatabaseProvider::class,

        \App\Providers\AppServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | エイリアス
    |--------------------------------------------------------------------------
    |
    | アプリケーション起動時に自動的にロードされるファサードのエイリアスをここに記載します。
    | ファサードは、コンテナーに登録されたサービスをstaticメソッドで呼び出すための簡単な方法です。
    | 例えば、$app['auth']->check() と書く代わりに、\Auth::check() と書くことができます。
    |
    */

    'aliases' => [
        'App' => \Phox\Support\Facades\Application::class,
        'DB' => \Phox\Support\Facades\DB::class,
        'Log' => \Phox\Support\Facades\Log::class,
        'Auth' => \Phox\Support\Facades\Auth::class,
        'Cache' => \Phox\Support\Facades\Cache::class,
        'Security' => \Phox\Support\Facades\Security::class,
        'Request' => \Phox\Support\Facades\Request::class,
        'Response' => \Phox\Support\Facades\Response::class,
        'Session' => \Phox\Support\Facades\Session::class,
        'DebugLogger' => \Phox\Support\Facades\DebugLogger::class,
        'Artisan' => \Phox\Support\Facades\Artisan::class,
        'ID' => \Phox\Support\Facades\Sqids::class,
        //'Route' => \Phox\Support\Facades\Route::class,
    ],
];
