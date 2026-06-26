import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Laravel Echo クライアント。Soketi (Pusher プロトコル互換) に接続する。
 * 設定は Vite env (VITE_PUSHER_*) から読む。鍵が未設定なら接続しない
 * （Soketi なしのローカルでコンソールエラーを出さないため）。
 */

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo?: Echo<'pusher'>;
    }
}

/** Inertia の初期ページ JSON から CSRF トークンを取り出す（セッション単位で安定）。 */
function csrfToken(): string {
    const el = document.getElementById('app');
    try {
        const page = JSON.parse(el?.dataset.page ?? '{}');
        return page?.props?.csrf_token ?? '';
    } catch {
        return '';
    }
}

const key = import.meta.env.VITE_PUSHER_APP_KEY as string | undefined;

let echo: Echo<'pusher'> | undefined;

if (key) {
    window.Pusher = Pusher;

    const scheme = (import.meta.env.VITE_PUSHER_SCHEME as string) ?? 'http';
    const port = Number(import.meta.env.VITE_PUSHER_PORT ?? 6001);

    echo = new Echo({
        broadcaster: 'pusher',
        key,
        cluster: (import.meta.env.VITE_PUSHER_APP_CLUSTER as string) ?? 'mt1',
        wsHost: (import.meta.env.VITE_PUSHER_HOST as string) ?? '127.0.0.1',
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: { headers: { 'X-CSRF-TOKEN': csrfToken() } },
    });

    window.Echo = echo;
}

export default echo;
