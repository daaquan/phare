@extends('layouts.auth')

@section('content')

  <div class="relative flex flex-col justify-center h-screen overflow-hidden">
    <div class="w-full p-6 m-auto bg-white rounded-lg shadow-md ring-2 ring-gray-800/50 lg:max-w-lg">
      <div class="flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
             class="w-7 h-7 text-green-600 mr-1">
          <path fill-rule="evenodd"
                d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                clip-rule="evenodd"/>
        </svg>
        <p class="text-2xl font-semibold text-center text-gray-700">
          @lang('labels.frontend.auth.login_box_title')
        </p>
      </div>

      <div class="divider"></div>

      @form(action=login method=post)
      @csrf

      <fieldset class="fieldset mb-3">
        <label class="fieldset-label" for="email">@lang('labels.frontend.auth.email')</label>
        @input(type="email" name="email" required placeholder="mail@example.com" class="input w-full")
      </fieldset>

      <fieldset class="fieldset mb-3">
        <label class="fieldset-label" for="password">@lang('labels.frontend.auth.password')</label>
        @input(type="password" name="password" required placeholder="Enter your password" class="input w-full")
      </fieldset>

      <div class="flex items-center gap-2 mb-4">
        @input(type="checkbox" name="remember_me" id="remember_me" class="checkbox checkbox-sm")
        <label for="remember_me">@lang('labels.frontend.auth.remember_me')</label>
      </div>

      <div class="text-center">
        @button(type="submit" text="@lang('labels.frontend.auth.submit')" class="btn w-full bg-gray-600 text-white hover:bg-gray-400")
      </div>

      @endform

    </div>
  </div>

@endsection
