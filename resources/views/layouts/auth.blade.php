<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" data-theme="winter">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport"
        content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,shrink-to-fit=no"/>
  <meta http-equiv="content-security-policy" content="default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self'; img-src 'self' data:;"/>
  <title>{{ $title }}</title>

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">


  @cssbox
  <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
  <style> body { visibility: hidden; } </style>
  <noscript><style> body { visibility: visible; } </style></noscript>
</head>
<body>
<div class="bg-base-100 drawer lg:drawer-open">

  <div class="drawer-content">

    <section class="h-screen">
      <div class="px-6 py-12 h-full">

        @yield('content')

      </div>
    </section>

  </div>

</div>

@include('partials.flash-messages')

<script type="application/javascript" src="{{ asset('assets/js/app.js') }}"></script>
@jsbox

<script>
  document.body.style.visibility = 'visible';
</script>

</body>
</html>
