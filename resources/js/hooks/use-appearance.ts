import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light' | 'dark' | 'system';

const STORAGE_KEY = 'appearance';

function prefersDark(): boolean {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function applyTheme(appearance: Appearance): void {
    const isDark =
        appearance === 'dark' || (appearance === 'system' && prefersDark());
    document.documentElement.classList.toggle('dark', isDark);
}

/**
 * Reads/writes the stored appearance, applies the `.dark` class, and follows
 * the system preference when set to "system".
 */
export function useAppearance() {
    const [appearance, setAppearanceState] = useState<Appearance>('system');

    const setAppearance = useCallback((value: Appearance) => {
        setAppearanceState(value);
        localStorage.setItem(STORAGE_KEY, value);
        applyTheme(value);
    }, []);

    useEffect(() => {
        const stored =
            (localStorage.getItem(STORAGE_KEY) as Appearance | null) ?? 'system';
        setAppearanceState(stored);
        applyTheme(stored);

        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const onChange = () => {
            if (
                (localStorage.getItem(STORAGE_KEY) as Appearance | null) ===
                'system'
            ) {
                applyTheme('system');
            }
        };
        media.addEventListener('change', onChange);

        return () => media.removeEventListener('change', onChange);
    }, []);

    return { appearance, setAppearance };
}
