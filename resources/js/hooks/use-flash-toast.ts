import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import { toast } from 'sonner';

import { type SharedProps } from '@/types';

/**
 * Surfaces the shared `flash` prop as a toast. Call once inside a layout that
 * every page renders through (AppLayout / GuestLayout).
 */
export function useFlashToast(): void {
    const { flash } = usePage<SharedProps>().props;

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash?.success, flash?.error]);
}
