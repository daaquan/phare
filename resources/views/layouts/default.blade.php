<html lang="{{ config('app.locale') }}" data-theme="winter">
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
          <div class="prose prose-sm md:prose-base w-full max-w-6xl flex-grow pt-10">

            @yield('content')

          </div>
        </div>
      </div>
    </div>

    <div class="drawer-side z-40" style="scroll-behavior: smooth; scroll-padding-top: 5rem;">
      <label for="drawer" class="drawer-overlay" aria-label="Close menu"></label>
      <aside class="bg-base-200 w-80">

        @include('partials.sidebar')

      </aside>
    </div>

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
