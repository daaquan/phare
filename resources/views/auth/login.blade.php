@extends('layouts.auth')

@section('content')

  <div class="flex justify-center items-center flex-wrap h-full g-6 text-gray-800">
    <div class="md:w-8/12 lg:w-6/12 mb-12 md:mb-0 text-right opacity-90">

      <h3 class="text-5xl font-bold text-white">@lang('labels.frontend.auth.login_box_title')</h3>
      <p class="py-6 text-white">@config('app.name') @config('app.title')</p>

    </div>

    <div class="md:w-8/12 lg:w-5/12 lg:ml-20">

      <div class="card flex-shrink-0 w-full max-w-sm shadow-2xl bg-base-100 bg-opacity-80 backdrop-blur">
        <div class="card-body">

          @form(action=login method=post)
          @csrf

          <div class="form-control"><label for="email" class="label">
              <span class="label-text">@lang('labels.frontend.auth.email')</span></label>
            @input(type="email" name="email" required placeholder="mail@example.com" class="input input-bordered")
          </div>

          <div class="form-control pt-2"><label for="password" class="label">
              <span class="label-text">@lang('labels.frontend.auth.password')</span></label>
            @input(type="password" name="password" required placeholder="Enter your password" class="input
            input-bordered")
          </div>

          <div class="form-control pt-2"><label for="remember_me" class="label form-check-label">
              @input(type="checkbox" name="remember_me" id="remember_me" class="checkbox checkbox-sm")
              <span class="label-text">@lang('labels.frontend.auth.remember_me')</span></label>
          </div>

          <div class="form-control mt-2">
            @button(type="submit" text="@lang('labels.frontend.auth.submit')" class="w-full flex justify-center
            bg-slate-800 hover:bg-slate-600 text-gray-100 p-3 rounded-full tracking-wide font-semibold shadow-lg
            cursor-pointer transition ease-in duration-500")
          </div>

          @endform

          @include('partials.copyright')

        </div>
      </div>

    </div>
  </div>

@endsection
