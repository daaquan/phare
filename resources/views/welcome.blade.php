@extends('layouts.auth')

@section('content')

  <div class="relative flex flex-col justify-center h-screen overflow-hidden">
    <div class="w-full p-8 m-auto bg-white rounded-lg shadow-md ring-2 ring-gray-800/50 lg:max-w-xl">

      <div class="flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
             class="w-10 h-10 text-indigo-600 mr-2">
          <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z"/>
          <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.284a.75.75 0 0 1-.46.711 47.87 47.87 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.87 47.87 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286a48.4 48.4 0 0 1 6.862 2.856l.775.413a1.876 1.876 0 0 0 1.761.07l.388-.21Z"/>
        </svg>
        <h1 class="text-3xl font-bold text-gray-800">PHPファン</h1>
      </div>

      <p class="text-center text-gray-600 mb-6">
        PHPで作るWebアプリケーションの世界へようこそ
      </p>

      <div class="divider"></div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="card bg-base-200 p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="ti ti-code text-xl text-indigo-500"></i>
            <h3 class="font-semibold">Phare Framework</h3>
          </div>
          <p class="text-sm text-gray-600">Phalcon上に構築された高速PHPフレームワーク</p>
        </div>

        <div class="card bg-base-200 p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="ti ti-bolt text-xl text-amber-500"></i>
            <h3 class="font-semibold">高パフォーマンス</h3>
          </div>
          <p class="text-sm text-gray-600">C拡張ベースのPhalconによる高速処理</p>
        </div>

        <div class="card bg-base-200 p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="ti ti-database text-xl text-green-500"></i>
            <h3 class="font-semibold">PostgreSQL</h3>
          </div>
          <p class="text-sm text-gray-600">堅牢なリレーショナルデータベース</p>
        </div>

        <div class="card bg-base-200 p-4">
          <div class="flex items-center gap-2 mb-2">
            <i class="ti ti-server text-xl text-red-500"></i>
            <h3 class="font-semibold">Redis</h3>
          </div>
          <p class="text-sm text-gray-600">高速キャッシュ・セッション管理</p>
        </div>
      </div>

      <div class="flex justify-center gap-4">
        <a href="/auth/login" class="btn bg-gray-600 text-white hover:bg-gray-400">
          <i class="ti ti-login mr-1"></i>ログイン
        </a>
      </div>

    </div>
  </div>

@endsection
