<html lang="{{ config('app.locale') }}" data-theme="winter">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport"
        content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,shrink-to-fit=no"/>
  <meta http-equiv="content-security-policy" content=""/>
  <title>{{ $title }}</title>

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">

  <script src="//cdn.tailwindcss.com?plugins=typography"></script>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/daisyui@4.10.1/dist/full.min.css">

  @cssbox
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">

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

<script type="application/javascript" src="{{ asset('js/app.js') }}"></script>
@jsbox

</body>
</html>
