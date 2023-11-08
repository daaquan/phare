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
  <link rel="icon" type="image/vnd.microsoft.icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAP//AAD//wAA//8AAA==">

  @cssbox

</head>
<body tabindex="-1">
<div class="bg-base-100 drawer drawer-mobile"><input id="drawer" type="checkbox" class="drawer-toggle">
  <div class="drawer-content" style="scroll-behavior: smooth; scroll-padding-top: 5rem;">
    <div class="sticky top-0 z-30 flex h-16 w-full justify-center bg-opacity-90 backdrop-blur
        transition-all duration-100 bg-base-100 text-base-content">

      <nav class="navbar w-full">
        <div class="flex flex-1 md:gap-1 lg:gap-2">
          @include('partials.sidebar-top')
        </div>
        <div class="flex-0">
          @include('partials.header')
        </div>
      </nav>
    </div>

    <div class="px-6 xl:pr-2 pb-16">
      <div class="flex flex-col-reverse justify-between gap-6 xl:flex-row">
        <div class="prose w-full max-w-8xl flex-grow">

          @yield('content')

        </div>
      </div>
    </div>
  </div>
  <div class="drawer-side" style="scroll-behavior: smooth; scroll-padding-top: 5rem;">
    <label for="drawer" class="drawer-overlay"></label>
    <aside class="bg-base-200 w-80">
      <div
          class="z-20 bg-base-200 bg-opacity-90 backdrop-blur sticky top-0 items-center gap-2 px-4 py-2 hidden lg:flex shadow-sm">
        <a href="/" aria-current="page" aria-label="Homepage" class="flex-0 btn btn-ghost px-2">
          <div class="font-title text-primary text-lg transition-all duration-200 md:text-3xl">
            <div class="uppercase">@config('app.name')</div>
            <div class="text-base-content uppercase text-sm">@config('app.title')</div>
          </div>
        </a> <a href="/docs/changelog" class="link link-hover font-mono text-xs text-opacity-50">
          <div data-tip="Changelog" class="tooltip tooltip-bottom">{{ \App::version() }}</div>
        </a></div>
      <div class="h-4"></div>

      @include('partials.sidebar')

      <ul class="menu menu-compact flex flex-col p-0 px-4"></ul>
      <div
          class="from-base-200 pointer-events-none sticky bottom-0 flex h-20 bg-gradient-to-t to-transparent"></div>
    </aside>
  </div>
</div>

@include('partials.debugbar')

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
