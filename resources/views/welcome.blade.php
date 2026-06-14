@extends('layouts.auth')

@section('content')

<div class="relative flex min-h-full flex-col selection:bg-red-500 selection:text-white">
    <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl mx-auto py-10">
        
        <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
            <div class="flex lg:justify-center lg:col-start-2">
                <svg class="h-12 w-auto text-red-500" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M61.8548 14.6253C61.8778 14.7102 61.8895 14.7978 61.8897 14.8858V28.5615C61.8898 28.737 61.8434 28.9095 61.7554 29.0614C61.6674 29.2133 61.5408 29.3393 61.3887 29.4265L49.9104 36.0351V49.1337C49.9104 49.4902 49.7211 49.8195 49.4118 49.9975L25.4519 63.7824C25.3971 63.8135 25.3382 63.8364 25.2784 63.8519C25.2752 63.8527 25.2719 63.8535 25.2687 63.8543C25.2072 63.8702 25.1441 63.8781 25.0808 63.8781C24.9531 63.8781 24.8262 63.8527 24.7075 63.8035C24.6973 63.7993 24.6874 63.7946 24.6775 63.7899C24.6211 63.7632 24.5665 63.7325 24.5142 63.6981L12.5418 55.8316C12.5352 55.8272 12.5288 55.8226 12.5225 55.8179C12.4705 55.7794 12.4221 55.7357 12.3779 55.6875C12.374 55.6832 12.3702 55.6788 12.3665 55.6743C12.3242 55.6234 12.2863 55.5687 12.2534 55.5108C12.2502 55.5053 12.2471 55.4997 12.2441 55.4941C12.2092 55.4296 12.1806 55.3617 12.1587 55.2912C12.1563 55.2836 12.154 55.2759 12.1518 55.2682C12.1285 55.1869 12.1135 55.1032 12.1071 55.0182C12.1064 55.0099 12.1058 55.0016 12.1053 54.9933C12.0984 54.8712 12.1053 54.7484 12.1259 54.6285L16.4421 29.5314V16.4328C16.4421 16.2545 16.5317 16.0878 16.6813 15.9902C16.6863 15.9869 16.6914 15.9838 16.6965 15.9808L28.1748 9.37218C28.3269 9.28498 28.5001 9.23883 28.6763 9.23883C28.8525 9.23883 29.0257 9.28498 29.1778 9.37218L49.4118 21.0472C49.5639 21.1344 49.6905 21.2604 49.7785 21.4123C49.8665 21.5642 49.9129 21.7367 49.9129 21.9122V35.0108L58.4853 30.065V16.9664C58.4853 16.7881 58.5749 16.6214 58.7245 16.5238C58.7295 16.5205 58.7346 16.5174 58.7397 16.5144L60.1448 15.705C60.2969 15.6178 60.4701 15.5716 60.6463 15.5716C60.8225 15.5716 60.9957 15.6178 61.1478 15.705L61.8548 16.1125C61.8622 16.1168 61.8695 16.1213 61.8767 16.126C62.0313 16.2264 62.1438 16.3804 62.1933 16.5591C62.1971 16.5729 62.2004 16.5869 62.2032 16.601C62.2038 16.6042 62.2043 16.6074 62.2048 16.6106C62.2431 16.8233 62.2152 17.0433 62.1252 17.2385L61.8548 14.6253ZM56.1648 28.5615V18.8728L49.9104 22.477V32.1657L56.1648 28.5615ZM46.1913 47.723V36.9025L28.6763 26.8055V13.7069L20.1039 18.6527V29.4732L15.7877 54.5703L25.0808 60.679L46.1913 47.723ZM37.6189 23.0414L46.1913 27.9872V17.1667L37.6189 12.2209V23.0414Z" fill="currentColor"/>
                </svg>
            </div>
            <div class="flex justify-end lg:col-start-3">
                @if (\Auth::check())
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900">
                        {{ __('welcome.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-md bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500">
                        {{ __('welcome.login') }}
                    </a>
                @endif
            </div>
        </header>

        <main class="mt-6">
            <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                <a href="https://laravel.com/docs" class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-gray-900/5 transition duration-300 hover:ring-gray-900/20 focus:outline-none focus-visible:ring-red-500 md:row-span-3 lg:p-10 lg:pb-10">
                    <div class="relative flex items-center gap-4 lg:gap-8">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-500/10 shrink-0">
                            <svg class="h-8 w-8 stroke-red-500 transition group-hover:rotate-[-4deg] group-hover:stroke-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('welcome.cards.docs.title') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('welcome.cards.docs.description') }}</p>
                        </div>
                    </div>
                </a>

                <a href="https://phalcon.io" class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-gray-900/5 transition duration-300 hover:ring-gray-900/20 focus:outline-none focus-visible:ring-red-500 md:row-span-3 lg:p-10 lg:pb-10">
                    <div class="relative flex items-center gap-4 lg:gap-8">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-red-500/10 shrink-0">
                            <svg class="h-8 w-8 stroke-red-500 transition group-hover:rotate-[-4deg] group-hover:stroke-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('welcome.cards.phalcon.title') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('welcome.cards.phalcon.description') }}</p>
                        </div>
                    </div>
                </a>

                <a href="https://github.com/daaquan/framework" class="flex flex-col items-start gap-6 overflow-hidden rounded-lg bg-white p-6 shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)] ring-1 ring-gray-900/5 transition duration-300 hover:ring-gray-900/20 focus:outline-none focus-visible:ring-red-500 md:row-span-3 lg:p-10 lg:pb-10">
                    <div class="relative flex items-center gap-4 lg:gap-8">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-900/10 shrink-0">
                            <svg class="h-8 w-8 stroke-gray-900 transition group-hover:rotate-[-4deg] group-hover:stroke-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H6A2.25 2.25 0 003.75 6v2.25A2.25 2.25 0 006 10.5zm0 9.75h2.25A2.25 2.25 0 0010.5 18v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25V18A2.25 2.25 0 006 20.25zm9.75-9.75H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6v2.25a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('welcome.cards.github.title') }}</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ __('welcome.cards.github.description') }}</p>
                        </div>
                    </div>
                </a>
            </div>
        </main>

        <footer class="py-16 text-center text-sm text-gray-500">
            {{ __('welcome.footer') }}
        </footer>
    </div>
</div>

@endsection
