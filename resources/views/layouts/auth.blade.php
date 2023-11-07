<html lang="en" data-theme="winter">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport"
        content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0,shrink-to-fit=no"/>
  <meta http-equiv="content-security-policy" content=""/>
  <title>{{ $title }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""/>
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet"/>

  @cssbox

</head>
<body tabindex="-1">
<div class="drawer drawer-mobile" style="background-image: url(/themes/uce/images/bg_cloud_pc.jpg);"><input
      id="drawer" type="checkbox" class="drawer-toggle">
  <div class="drawer-content" style="scroll-behavior: smooth; scroll-padding-top: 5rem;">
    <div class="sticky top-0 z-30 flex h-16 w-full justify-center bg-opacity-50 backdrop-blur
        transition-all duration-100 bg-base-100 text-base-content">

      <nav class="navbar w-full">
        <div class="flex flex-1 md:gap-1 lg:gap-2"></div>
        <div class="flex-0">

          @include('partials.header')

        </div>
      </nav>
    </div>
    <section class="h-screen">
      <div class="px-6 py-12 h-full">

        @yield('content')

      </div>
    </section>

  </div>
</div>

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
