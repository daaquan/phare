<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>

  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">

  <title inertia>{{ config('app.name', 'Phare') }}</title>

  {{-- Apply the stored theme before first paint to avoid a flash. --}}
  <script>
    (function () {
      var a = localStorage.getItem('appearance') || 'system';
      var dark = a === 'dark' || (a === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.classList.toggle('dark', dark);
    })();
  </script>

  @viteReactRefresh
  @vite(['resources/css/app.css', 'resources/js/app.tsx'])
  @inertiaHead
</head>
<body class="antialiased">
@inertia
</body>
</html>
