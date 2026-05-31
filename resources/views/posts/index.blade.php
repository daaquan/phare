@extends('layouts.default')

@section('content')

  <h1>{{ $title }}</h1>

  @if ($posts->total() > 0)
    <div class="overflow-x-auto">
      <table class="table m-0">
        <thead>
        <tr>
          <th>ID</th>
          <th>タイトル</th>
          <th>投稿者</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($posts as $post)
          <tr>
            <th>{{ $post->id }}</th>
            <td>{{ $post->title }}</td>
            <td>{{ $post->user?->name ?? '—' }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <div class="my-6 join">
      {!! $posts->links() !!}
    </div>
  @else
    <p>投稿がありません。</p>
  @endif

@endsection
