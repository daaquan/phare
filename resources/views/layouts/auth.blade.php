<html lang="{{ config('app.locale') }}" data-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport"
        content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,shrink-to-fit=no"/>
  <meta http-equiv="content-security-policy" content=""/>
  <title>{{ $title }}</title>
  <link media="screen and (min-width: 520px)" rel="preconnect" href="https://fonts.googleapis.com"/>
  <link media="screen and (min-width: 520px)" rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link media="screen and (min-width: 520px)"
        href="https://fonts.googleapis.com/css2?family=Figtree:wght@300;900&amp;family=Noto+Sans+JP:wght@300;900&amp;family=Noto+Sans:wght@300;900&amp;family=Vazirmatn:wght@300;900&amp;display=swap"
        rel="stylesheet">

  @cssbox

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

@jsbox

<script type="text/javascript">
  // prevent double submit
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', event => {
      const submitButton = form.querySelector('[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
      }
    });
  });

  // prevent resubmit on back button
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }
</script>

</body>
</html>
