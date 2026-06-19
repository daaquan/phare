import { Toaster as Sonner, type ToasterProps } from 'sonner';

import { useAppearance } from '@/hooks/use-appearance';

/**
 * Toast container wired to the app's appearance (light/dark/system).
 * Mounted once at the app root; `toast()` calls work from anywhere.
 */
export function Toaster(props: ToasterProps) {
    const { appearance } = useAppearance();

    return (
        <Sonner
            theme={appearance as ToasterProps['theme']}
            className="toaster group"
            position="top-center"
            richColors
            {...props}
        />
    );
}
