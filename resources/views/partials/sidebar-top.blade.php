<span
    class="tooltip tooltip-bottom before:text-xs before:content-[attr(data-tip)]"
    data-tip="Menu"><label for="drawer" class="btn btn-square btn-ghost drawer-button lg:hidden"><svg
        width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24" class="inline-block h-5 w-5 stroke-current md:h-6 md:w-6"><path
          stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 6h16M4 12h16M4 18h16"></path></svg></label></span>
<div class="flex items-center gap-2 lg:hidden"><a href="/" aria-current="page" aria-label="Homepage"
                                                  class="flex-0 btn btn-ghost px-2 ">
    <div
        class="font-title text-primary inline-flex text-lg transition-all duration-200 md:text-3xl">
      <span class="uppercase text-primary">@config('app.name')</span> <span
          class="uppercase text-base-content">@config('app.title')</span></div>
  </a> <a href="/docs/changelog" class="link link-hover font-mono text-xs text-opacity-50 ">
    <div data-tip="Changelog" class="tooltip tooltip-bottom">{{ \App::version() }}</div>
  </a></div>

<div class="hidden w-full max-w-sm lg:flex">
  <label class="searchbox relative mx-3 w-full">
    <svg
        class="pointer-events-none absolute z-10 my-3 ml-2 mt-4 stroke-current opacity-60 text-base-content"
        width="16" height="16" xmlns="http://www.w3.org/2000/svg" fill="none"
        viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
    <div data-svelte-typeahead="" role="combobox" aria-haspopup="listbox">
      <form data-svelte-search="">
        <input name="search" type="search" placeholder="@lang('strings.backend.search.empty')"
               autocomplete="off" spellcheck="false" aria-autocomplete="list"
               class="input input-no-border w-full pl-8"></form>
    </div>
  </label>
</div>
