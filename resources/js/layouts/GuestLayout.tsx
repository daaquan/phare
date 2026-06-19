import { type PropsWithChildren } from 'react';

import { useFlashToast } from '@/hooks/use-flash-toast';

export default function GuestLayout({ children }: PropsWithChildren) {
    useFlashToast();

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-muted px-6 py-12">
            <div className="w-full max-w-md">{children}</div>
        </div>
    );
}
