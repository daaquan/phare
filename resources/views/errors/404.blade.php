@extends('layouts.auth')

@php($title = __('http.404.title'))

@section('content')

  <div class="flex min-h-screen flex-col items-center justify-center px-4 text-center">
    <p class="text-7xl font-bold text-indigo-600">404</p>
    <h1 class="mt-4 text-2xl font-semibold text-gray-800">{{ __('http.404.title') }}</h1>
    <p class="mt-2 max-w-md text-gray-600">{!! __('http.404.description') !!}</p>

    <a href="{{ route('welcome') }}" class="btn btn-primary mt-8">
      <i class="ti ti-home mr-1" aria-hidden="true"></i>{{ __('welcome.brand') }}
    </a>
  </div>

@endsection
