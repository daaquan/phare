<?php

use App\Providers\AppServiceProvider;
use App\Providers\SqidsServiceProvider;
use Phare\Broadcasting\BroadcastServiceProvider;
use Phare\Inertia\InertiaServiceProvider;
use Phare\Mail\MailServiceProvider;
use Phare\Providers\AuthServiceProvider;
use Phare\Providers\DatabaseProvider;
use Phare\Providers\DebugLoggerProvider;
use Phare\Providers\DebugWhoopsProvider;
use Phare\Providers\DispatcherProvider;
use Phare\Providers\EncrypterProvider;
use Phare\Providers\ErrorHandlerProvider;
use Phare\Providers\EventsManagerProvider;
use Phare\Providers\LogServiceProvider;
use Phare\Providers\ModelProvider;
use Phare\Providers\QueueServiceProvider;
use Phare\Providers\RequestProvider;
use Phare\Providers\ResponseProvider;
use Phare\Providers\RouteServiceProvider;
use Phare\Providers\SessionProvider;
use Phare\Providers\TranslateProvider;
use Phare\Support\Facades\Application;
use Phare\Support\Facades\Artisan;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Broadcast;
use Phare\Support\Facades\Cache;
use Phare\Support\Facades\DB;
use Phare\Support\Facades\DebugLogger;
use Phare\Support\Facades\Inertia;
use Phare\Support\Facades\Log;
use Phare\Support\Facades\Request;
use Phare\Support\Facades\Response;
use Phare\Support\Facades\Security;
use Phare\Support\Facades\Session;
use Phare\Support\Facades\Sqids;
use Phare\View\ViewServiceProvider;

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
        'orm' => [
            'enable_implicit_joins' => false, // モデル間の関連を使用して、暗黙の結合を有効にするかどうか
            'exception_on_failed_save' => true, // モデルの保存に失敗した場合に例外をスローするかどうか
            'force_casting' => false, // データベースから取得した値をキャストするかどうか
            'ignore_unknown_columns' => true, // モデルに定義されていないカラムを無視するかどうか
            'not_null_validations' => true, // モデルのプロパティがNOT NULLである場合に、NULLを許可するかどうか
            'resultset_prefetch_records' => '0', // プリフェッチするレコード数
            'update_snapshot_on_save' => true, // モデルのスナップショットを更新するかどうか
            'virtual_foreign_keys' => false, // 仮想外部キーを有効にするかどうか
            // optional
            'cache_level' => 3, // 0: キャッシュしない, 1: メタデータのみキャッシュ, 2: メタデータと結果セットをキャッシュ, 3: メタデータと結果セットをキャッシュし、キャッシュを使用してクエリを作成
            'case_insensitive_column_map' => false, // カラム名をキーとする配列を作成する際に、キーを小文字にするかどうか
            'cast_last_insert_id_to_int' => false, // 最後に挿入されたIDを整数にキャストするかどうか
            'cast_on_hydrate' => false, // ハイドレーション時に値をキャストするかどうか
            'column_renaming' => true, // カラム名を変更するかどうか
            'disable_assign_setters' => false, // プロパティに値を設定する際に、セッターを使用するかどうか
            'enable_literals' => true, // リテラルオブジェクトを有効にするかどうか
            'events' => true, // イベントを有効にするかどうか
            'exception_on_failed_metadata_save' => true, // メタデータの保存に失敗した場合に例外をスローするかどうか
            'late_state_binding' => false, // Late state binding of the Phalcon\Mvc\Model::cloneResultMap() method
            'unique_cache_id' => 3, // キャッシュIDのユニーク性を保証するための値
        ],
        'db' => [
            'escape_identifiers' => 'On', // クエリの識別子をエスケープする
            'force_casting' => 'Off', // データベースから取得した値をキャストする
        ],
        'warning.enable' => true, // ワーニングを有効にする
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
        LogServiceProvider::class,
        EventsManagerProvider::class,
        ErrorHandlerProvider::class,
        DispatcherProvider::class,
        DebugLoggerProvider::class,
        EncrypterProvider::class,
        // \Phare\Providers\ChronosProvider::class,
        // \Phare\Providers\SqidsProvider::class,
        RouteServiceProvider::class,
        RequestProvider::class,
        ResponseProvider::class,
        SessionProvider::class,
        AuthServiceProvider::class,
        ModelProvider::class,
        DatabaseProvider::class,
        QueueServiceProvider::class,
        BroadcastServiceProvider::class,

        DebugWhoopsProvider::class,
        ViewServiceProvider::class,
        InertiaServiceProvider::class,
        MailServiceProvider::class,
        TranslateProvider::class,

        AppServiceProvider::class,
        SqidsServiceProvider::class,
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
        'App' => Application::class,
        'DB' => DB::class,
        'Log' => Log::class,
        'Auth' => Auth::class,
        'Cache' => Cache::class,
        'Security' => Security::class,
        'Request' => Request::class,
        'Response' => Response::class,
        'Session' => Session::class,
        'DebugLogger' => DebugLogger::class,
        'Artisan' => Artisan::class,
        'ID' => Sqids::class,
        'Inertia' => Inertia::class,
        'Broadcast' => Broadcast::class,
        // 'Route' => \Phare\Support\Facades\Route::class,
    ],
];
