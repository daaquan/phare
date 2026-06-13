<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" data-theme="winter">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport"
        content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,shrink-to-fit=no"/>
  <meta http-equiv="content-security-policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:;"/>
  <title>{{ $title ?? 'Phare' }}</title>

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">


  @cssbox
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <style> body { visibility: hidden; } </style>
  <noscript><style> body { visibility: visible; } </style></noscript>
</head>
<body>
<div>

  <div class="bg-base-100 drawer lg:drawer-open">
    <input id="drawer" type="checkbox" class="drawer-toggle">

    <div class="drawer-content">
      <div
          class="bg-base-100 text-base-content sticky top-0 z-30 flex h-16
          w-full justify-center bg-opacity-90 backdrop-blur
          transition-shadow duration-100 [transform:translate3d(0,0,0)]">
        <nav class="navbar w-full">

          @include('partials.header')

        </nav>
      </div>
      <div class="max-w-[100vw] px-6 pb-16 xl:pr-2">
        <div class="flex flex-col-reverse justify-between gap-6 xl:flex-row">
          <div class="prose prose-sm md:prose-base w-full max-w-6xl flex-grow pt-5">

            @yield('content')

          </div>
        </div>
      </div>
    </div>

    <div class="drawer-side z-40">
      <label for="drawer" class="drawer-overlay" aria-label="Close menu"></label>
      <aside class="bg-base-200 w-80">

        @include('partials.sidebar')

      </aside>
    </div>

  </div>
</div>

@include('partials.flash-messages')
@jsbox

<script type="application/javascript" src="{{ asset('js/app.js') }}"></script>

<script>
  document.body.style.visibility = 'visible';
</script>

</body>
</html>
