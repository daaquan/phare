@extends('layouts.default')

@section('content')

  <h1>@lang('pages.index.title')</h1>

  <p>@yield('content')</p>

  <p>@lang('pages.index.description')</p>

  <div class="not-prose mt-6 mb-10 overflow-x-auto">
    <table class="table-compact table w-full">
      <thead>
      <tr>
        <th class="flex items-center gap-2 normal-case">
          <div class="tooltip tooltip-right font-normal" data-tip="Add custom prefix"><input
                class="input input-bordered input-xs w-min max-w-[3.8rem]" type="text"
                placeholder="Prefix–"></div>
          <span>クラス名</span></th>
        <th class="normal-case">カテゴリ</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <th class="font-normal"><span class="font-mono lowercase">mockup-window</span></th>
        <td><span class="badge badge-sm badge-ghost w-20">コンポーネント</span></td>
        <td>Container element</td>
      </tr>
      </tbody>
    </table>
  </div>
  <div class="component-preview not-prose text-base-content my-4 max-w-8xl"
       id="window-mockup-with-border">
    <div class="pb-2 text-sm font-bold"><a class="opacity-20 hover:opacity-60"
                                           href="#window-mockup-with-border">#</a> <span
          class="component-preview-title">window mockup with border</span></div>
    <div class="grid">
      <div class="tabs z-10 -mb-px">
        <button class="tab tab-lifted tab-active [--tab-bg:hsl(var(--b2))]">プレビュー</button>
        <button class="tab tab-lifted [--tab-border-color:transparent]">HTML</button>
        <button class="tab tab-lifted [--tab-border-color:transparent]">JSX</button>
        <div
            class="tab tab-lifted mr-6 flex-1 cursor-default [--tab-border-color:transparent]"></div>
      </div>
      <div class="bg-base-300 rounded-b-box rounded-tr-box relative overflow-x-auto">
        <div
            class="preview border-base-300 bg-base-200 rounded-b-box rounded-tr-box flex min-h-[6rem] min-w-[18rem] max-w-8xl flex-wrap items-center justify-center gap-2 overflow-x-hidden border bg-cover bg-top p-4 undefined"
            style="background-size: 5px 5px">
          <div class="border mockup-window border-base-300 w-full">
            <div class="flex justify-center px-4 py-16 border-t border-base-300">Hello!
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="component-preview not-prose text-base-content my-4 max-w-8xl"
       id="window-mockup-with-background-color">
    <div class="pb-2 text-sm font-bold"><a class="opacity-20 hover:opacity-60"
                                           href="#window-mockup-with-background-color">#</a> <span
          class="component-preview-title">window mockup with background color</span></div>
    <div class="grid">
      <div class="tabs z-10 -mb-px">
        <button class="tab tab-lifted tab-active [--tab-bg:hsl(var(--b2))]">プレビュー</button>
        <button class="tab tab-lifted [--tab-border-color:transparent]">HTML</button>
        <button class="tab tab-lifted [--tab-border-color:transparent]">JSX</button>
        <div
            class="tab tab-lifted mr-6 flex-1 cursor-default [--tab-border-color:transparent]"></div>
      </div>
      <div class="bg-base-300 rounded-b-box rounded-tr-box relative overflow-x-auto">
        <div
            class="preview border-base-300 bg-base-200 rounded-b-box rounded-tr-box flex min-h-[6rem] min-w-[18rem] max-w-8xl flex-wrap items-center justify-center gap-2 overflow-x-hidden border bg-cover bg-top p-4 undefined"
            style="background-size: 5px 5px">
          <div class="border mockup-window bg-base-300 w-full">
            <div class="flex justify-center px-4 py-16 bg-base-200">Hello!</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="not-prose pb-10">
    <div class="bg-base-content/10 my-10 mx-1 h-px"></div>
    <div class="flex justify-between">
      <div><a href="/components/mockup-phone"
              class="btn btn-sm md:btn-md btn-ghost gap-2 normal-case lg:gap-3">
          <svg class="h-6 w-6 fill-current md:h-8 md:w-8" xmlns="http://www.w3.org/2000/svg"
               width="24" height="24" viewBox="0 0 24 24">
            <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
          </svg>
          <div class="flex flex-col items-start"><span
                class="text-base-content/50 hidden text-xs font-normal md:block">前へ</span>
            <span>Phone スマホ</span></div>
        </a></div>
      <div><a href="/codepen" class="btn btn-sm md:btn-md gap-2 normal-case lg:gap-3">
          <div class="flex flex-col items-end"><span
                class="text-neutral-content/50 hidden text-xs font-normal md:block">次へ</span>
            <span>CodePen のサンプルページ</span></div>
          <svg class="h-6 w-6 fill-current md:h-8 md:w-8" xmlns="http://www.w3.org/2000/svg"
               width="24" height="24" viewBox="0 0 24 24">
            <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
          </svg>
        </a></div>
    </div>
    <div class="bg-base-content/10 my-10 mx-1 h-px"></div>
  </div>

@endsection
