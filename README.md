# Phox Framework

Phalconフレームワークをベースに開発された、スケーラブルで高機能なPHPゲームフレームワークです。

**キーワード:** phalcon, framework

## 概要

Phox Frameworkは、モダンなPHP開発プラクティスとPhalconのパフォーマンスを組み合わせ、堅牢で効率的なゲームアプリケーションバックエンドの構築を支援します。API開発からWebアプリケーションまで、幅広いニーズに対応可能な設計となっています。

## 主な機能

Phox Frameworkは、迅速なアプリケーション開発をサポートするための豊富な機能を提供します。

* **Phalconベース**: 高パフォーマンスなC言語拡張であるPhalcon (v5.4~) をコアに採用しています。
* **モジュールシステム**: アプリケーションをAPIやWebなどのモジュール単位で分割し、管理することができます。
* **ルーティング**:
    * コントローラーのアトリビュート (`#[Route]`, `#[RoutePrefix]`) を使用した直感的なルーティング定義が可能です。
    * 従来のファイルベースのルーティング定義もサポートしています。
    * パフォーマンス向上のためのルートキャッシュ機能を備えています。
* **データベースアクセス**:
    * Eloquentライクな直感的でパワフルなORMを提供します。
    * MySQL, PostgreSQL, SQLiteなど、複数のデータベースシステムをサポートしています。
    * データベースシャーディングに対応し、大規模データ処理を考慮した設計が可能です。
    * タイムスタンプ (`created_at`, `updated_at`) やソフトデリート などの便利な機能も提供します。
* **テンプレートエンジン**:
    * BladeOneをベースとしたカスタムBladeテンプレートエンジンを搭載し、シンプルで強力なビュー作成が可能です。
    * HTML生成を容易にするためのカスタムBladeディレクティブ (フォーム要素、CSSフレームワークコンポーネント等) を提供します。
    * Phalcon標準のVoltテンプレートエンジンも利用可能です。
* **認証機構**: セッションベースのユーザー認証システムを簡単に構築できます。
* **セッション管理**: ファイル、Redis (クラスタ構成含む) をバックエンドとした柔軟なセッション管理を実現します。
* **キャッシュシステム**: ファイル、Redis、APCなど複数のドライバをサポートするキャッシュ機能を提供します。
* **キューイングシステム**: Beanstalkdを利用した非同期タスク処理のためのキューイングシステムを統合しています。
* **エラーハンドリングとロギング**:
    * 堅牢な例外処理と、開発時に詳細な情報を提供するデバッグ機能を備えています。
    * 設定可能なマルチチャネルロギングシステム (ファイル、Syslog、標準エラー出力など) を提供します。
* **高機能なテストスイート**:
    * PHPUnitおよびPest PHPに対応したテスト環境を提供し、単体テストや機能テストの作成を容易にします。
    * HTTPリクエストのシミュレーションやレスポンス検証のための便利なアサーションメソッドを提供します。
* **開発支援ツール**:
    * Laravel PintによるPSR-12準拠のコードスタイル自動修正機能を統合しています。
    * PHP Insightsによる静的コード解析をサポートし、コード品質の維持向上に貢献します。
* **便利なユーティリティ**:
    * Chronosを拡張した日付時刻操作クラス (`Phox\Support\Chronos`) を提供します。
    * Sqidsを利用したIDエンコード/デコード機能を提供します。
    * 配列、コレクション、文字列操作を補助する豊富なヘルパークラス (`Arr`, `Collection`, `Str`) を提供します。
    * Symfony DotEnvコンポーネントを利用した安全な環境変数管理を行います。
* **サービスコンテナとFacade**:
    * 堅牢なDIコンテナによる依存性の管理と解決を行います。
    * 主要なサービスへ簡単にアクセスするためのFacadeパターンを採用しています。
* **ヘルパー関数**: `config()`, `env()`, `app()`, `response()`, `request()`, `route()`, `session()`, `now()` など、開発を効率化する多数のグローバルヘルパー関数を提供します。

## システム要件

* PHP ^8.2
* 必須PHP拡張:
    * `ext-mbstring`
    * `ext-openssl`
    * `ext-intl`
    * `ext-pdo`
    * `ext-gmp` (Sqidsで推奨)
    * `ext-bcmath` (Sqidsで推奨)
    * `ext-phalcon` (~5.4)
    * `ext-sqids` (推奨、IDジェネレータ用)
    * `ext-chronos` (推奨、Chronos DateTimeライブラリ用)
* 推奨PHP拡張:
    * `ext-redis` (Redisキャッシュ/セッション使用時)
    * `ext-msgpack` (msgpackシリアライザ使用時)

## インストール

1.  **リポジトリのクローン (またはダウンロード)**:
    ```bash
    git clone <repository_url> phox-framework
    cd phox-framework
    ```
2.  **Composer依存関係のインストール**:
    ```bash
    composer install
    ```
3.  **環境設定**:
    * `.env.example` ファイルをコピーして `.env` ファイルを作成します。
    * `.env` ファイル内のデータベース接続情報、アプリケーションキー (`APP_KEY`) などを適切に設定します。 `APP_KEY` は通常、32文字のランダムな文字列です。
        ```bash
        # 例: APP_KEYの生成 (もし `php artisan key:generate` のようなコマンドがあればそれを使用)
        # openssl rand -base64 32
        ```
4.  **ディレクトリパーミッション**:
    `storage` ディレクトリおよび `bootstrap/cache` ディレクトリに書き込み権限があることを確認してください。

## 基本的な使い方 (概念)

Phox Frameworkは、一般的なMVCライクなアーキテクチャを採用しています。

### 1. アプリケーションの起動

フレームワークは、`bootstrap/app.php` ファイルを通じてアプリケーションインスタンスを生成し、必要なサービスプロバイダをロードします。

### 2. ルーティング

ルートは `routes/` ディレクトリ内のファイル (例: `api.php`, `web.php`) やコントローラーのアトリビュートで定義します。

**例: アトリビュートベースのルーティング (`app/Http/Controllers/Api/ExampleController.php`)**

```php
<?php

namespace App\Http\Controllers\Api;

use Phox\Attributes\Route;
use Phox\Attributes\RoutePrefix;
use Phox\Http\Request; // PhoxのRequestクラスを想定
use App\Models\User; // あなたのUserモデルを想定

#[RoutePrefix('/users')]
class UserController
{
    #[Route('/', methods: ['GET'])]
    public function index(): array
    {
        return User::all()->toArray();
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): array
    {
        $user = User::firstOrFail($id);
        return $user->toArray();
    }

    #[Route('/', methods: ['POST'])]
    public function store(Request $request): array
    {
        // バリデーションを行う場合 (Phox\Http\Request にバリデーション機能がある場合)
        // $validatedData = $request->validate([
        // 'name' => 'required|string|max:255',
        // 'email' => 'required|email|unique:users',
        // 'password' => 'required|min:8',
        // ]);
        // $user = User::create($validatedData);

        // シンプルな例
        $user = new User();
        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->password = bcrypt($request->get('password')); // bcryptヘルパーを想定
        $user->save();

        return ['message' => 'User created successfully', 'user_id' => $user->id];
    }
}
```

### 3. モデル

データベースとのやり取りは、Eloquentライクなモデルを通じて行います。

**例: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Phox\Eloquent\Model;
use Phox\Auth\Authenticatable; // PhoxのAuthenticatableトレイトを想定
use Phox\Contracts\Auth\Authenticatable as AuthenticatableContract; // PhoxのContractを想定
use Phox\Eloquent\Concerns\HasTimestamps;

class User extends Model implements AuthenticatableContract
{
    use Authenticatable;
    use HasTimestamps; // created_at と updated_at を自動管理

    protected ?string $connection = 'mysql'; // config/database.php で定義した接続名
    protected ?string $table = 'users';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'name',
        'email',
        'password',
        // その他fillableなカラム
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

### 4. ビュー (Blade)

HTMLのレンダリングにはBladeテンプレートエンジンを使用します。

**例: `resources/views/users/index.blade.php`**

```blade
@extends('layouts.app') {{-- layouts/app.blade.php を継承 --}}

@section('title', 'User List')

@section('content')
    <h1>User List</h1>
    @if(count($users) > 0)
        <ul>
            @foreach($users as $user)
                <li>{{ $user->name }} ({{ $user->email }})</li>
            @endforeach
        </ul>
    @else
        <p>No users found.</p>
    @endif

    {{-- カスタムHTMLヘルパーの使用例 (Phox\View\Tags\BladeHtml が提供する場合) --}}
    {{-- @button(type="button" class="btn-primary" text="Add New User" href=@route('users.create')) --}}
@endsection
```

## テスト

Phox FrameworkはPHPUnitとPest PHPによるテストをサポートしています。

* **PHPUnitテストの実行**:
    ```bash
    ./vendor/bin/phpunit
    ```
* **Pest PHPテストの実行**:
    ```bash
    ./vendor/bin/pest
    ```

テストケースは `tests/` ディレクトリに配置します。
`tests/Unit` に単体テスト、`tests/Feature` (またはプロジェクト構成による) に機能テストを配置するのが一般的です。

## コードスタイル

本プロジェクトでは、コードスタイルの一貫性を保つためにLaravel Pintを使用しています。
コミット前に以下のコマンドを実行してコードスタイルを整形することを推奨します。

```bash
./vendor/bin/pint
```

## 静的解析

PHP Insightsを利用した静的コード解析も設定されています。
コードの品質をチェックするために、以下のコマンドを実行できます。

```bash
./vendor/bin/phpinsights
```

## 貢献

貢献を歓迎します！バグ報告、機能提案、プルリクエストはGitHubリポジトリのIssuesやPull Requestsセクションにお願いします。

1.  リポジトリをフォークしてください。
2.  フィーチャーブランチを作成してください (`git checkout -b feature/AmazingFeature`)。
3.  変更をコミットしてください (`git commit -m 'Add some AmazingFeature'`)。
4.  ブランチにプッシュしてください (`git push origin feature/AmazingFeature`)。
5.  プルリクエストを作成してください。
