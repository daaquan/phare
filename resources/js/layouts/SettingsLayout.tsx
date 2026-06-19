import { type PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';

import AppLayout from '@/layouts/AppLayout';
import { cn } from '@/lib/utils';

const tabs = [
    { label: 'Profile', href: '/settings/profile' },
    { label: 'Security', href: '/settings/security' },
    { label: 'Appearance', href: '/settings/appearance' },
];

interface SettingsLayoutProps {
    title: string;
}

export default function SettingsLayout({
    title,
    children,
}: PropsWithChildren<SettingsLayoutProps>) {
    const { url } = usePage();

    return (
        <AppLayout title="Settings">
            <div className="flex flex-col gap-8 lg:flex-row">
                <nav className="flex shrink-0 gap-1 lg:w-48 lg:flex-col">
                    {tabs.map((tab) => (
                        <Link
                            key={tab.href}
                            href={tab.href}
                            className={cn(
                                'rounded-md px-3 py-2 text-sm font-medium transition-colors',
                                url.startsWith(tab.href)
                                    ? 'bg-accent text-accent-foreground'
                                    : 'hover:bg-accent hover:text-accent-foreground',
                            )}
                        >
                            {tab.label}
                        </Link>
                    ))}
                </nav>

                <section className="max-w-xl flex-1">
                    <h2 className="mb-6 text-lg font-semibold">{title}</h2>
                    {children}
                </section>
            </div>
        </AppLayout>
    );
}
