<div class="flex flex-1 md:gap-1 lg:gap-2">
   <span class="tooltip tooltip-bottom before:text-xs before:content-[attr(data-tip)]" data-tip="Menu">
      <label aria-label="Open menu" for="drawer" class="btn btn-square btn-ghost drawer-button lg:hidden ">
         <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
              viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current md:h-6 md:w-6">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
         </svg>
      </label>
   </span>
  <div class="hidden w-full max-w-sm lg:flex">
    <label class="searchbox relative mx-3 w-full">
      <svg class="pointer-events-none absolute z-10 my-3.5 ms-4 mt-7 stroke-current opacity-60 text-base-content"
           width="16"
           height="16" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
      <div>
        @form(action=search method=post)
        @csrf
        @input(type="search" name="search" autocomplete="off" required placeholder=""
        class="input input-sm input-bordered rounded-md w-full pl-10 mt-5 max-w-xs")
        @endform
      </div>
    </label>
  </div>
</div>

<div class="flex-0">
  @lang('pages.greetings', ['name' => Auth::user()->name])
</div>

<div class="flex-0">
  <div title="Change Language" class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-ghost" aria-label="Language">
      <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512">
        <path d="M363,176,246,464h47.24l24.49-58h90.54l24.49,58H480ZM336.31,362,363,279.85,389.69,362Z"></path>
        <path
            d="M272,320c-.25-.19-20.59-15.77-45.42-42.67,39.58-53.64,62-114.61,71.15-143.33H352V90H214V48H170V90H32v44H251.25c-9.52,26.95-27.05,69.5-53.79,108.36-32.68-43.44-47.14-75.88-47.33-76.22L143,152l-38,22,6.87,13.86c.89,1.56,17.19,37.9,54.71,86.57.92,1.21,1.85,2.39,2.78,3.57-49.72,56.86-89.15,79.09-89.66,79.47L64,368l23,36,19.3-11.47c2.2-1.67,41.33-24,92-80.78,24.52,26.28,43.22,40.83,44.3,41.67L255,362Z"></path>
      </svg>
      <svg width="12px" height="12px" class="hidden h-2 w-2 fill-current opacity-60 sm:inline-block"
           xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2048 2048">
        <path d="M1799 349l242 241-1017 1017L7 590l242-241 775 775 775-775z"></path>
      </svg>
    </div>
    <div tabindex="0" class="dropdown-content bg-base-200 text-base-content rounded-box top-px mt-16
         max-h-[calc(100vh-10rem)] w-56 overflow-y-auto border border-white/5 shadow-2xl outline outline-1 outline-black/5">
      <ul class="menu menu-sm gap-1">
        <li>
          <a href="?lang=en"><span
                class="badge badge-sm badge-outline !pl-1.5 !pr-1 pt-px font-mono !text-[.6rem] font-bold tracking-widest opacity-50">EN</span>
            <span class="font-[sans-serif]">English</span></a>
        </li>
        <li>
          <a href="?lang=ja" class="active"><span
                class="badge badge-sm badge-outline !pl-1.5 !pr-1 pt-px font-mono !text-[.6rem] font-bold tracking-widest opacity-50">JA</span>
            <span class="font-[sans-serif]">日本語</span></a>
        </li>
      </ul>
    </div>
  </div>
</div>
