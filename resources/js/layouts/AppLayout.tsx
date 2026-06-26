import { type PropsWithChildren } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import { LayoutDashboard, FileText, LogOut, Radio, Settings } from 'lucide-react';

import {
    Avatar,
    AvatarFallback,
} from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useFlashToast } from '@/hooks/use-flash-toast';
import { type SharedProps } from '@/types';

interface AppLayoutProps {
    title?: string;
}

const navItems = [
    { label: 'Dashboard', href: '/dashboard', icon: LayoutDashboard },
    { label: 'Posts', href: '/posts', icon: FileText },
    { label: '設定', href: '/settings/profile', icon: Settings },
];

export default function AppLayout({
    title,
    children,
}: PropsWithChildren<AppLayoutProps>) {
    const { auth } = usePage<SharedProps>().props;
    const initial = auth.user?.name?.charAt(0).toUpperCase() ?? '?';

    const items = auth.user?.is_admin
        ? [
              ...navItems,
              {
                  label: 'Broadcasting',
                  href: '/admin/broadcasting',
                  icon: Radio,
              },
          ]
        : navItems;

    useFlashToast();

    return (
        <div className="flex min-h-screen bg-background text-foreground">
            <aside className="hidden w-64 shrink-0 border-r bg-sidebar text-sidebar-foreground lg:flex lg:flex-col">
                <div className="flex h-16 items-center px-6 text-lg font-semibold">
                    Phare
                </div>
                <nav className="flex-1 space-y-1 px-3 py-2">
                    {items.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
                        >
                            <item.icon className="size-4" />
                            {item.label}
                        </Link>
                    ))}
                </nav>
            </aside>

            <div className="flex flex-1 flex-col">
                <header className="flex h-16 items-center justify-between border-b px-6">
                    <h1 className="text-base font-semibold">{title}</h1>
                    <DropdownMenu>
                        <DropdownMenuTrigger className="outline-none">
                            <Avatar>
                                <AvatarFallback>{initial}</AvatarFallback>
                            </Avatar>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                {auth.user?.name}
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                variant="destructive"
                                onSelect={() => router.post('/user/logout')}
                            >
                                <LogOut className="size-4" />
                                ログアウト
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </header>

                {auth.user && !auth.user.email_verified && (
                    <div className="flex items-center justify-between gap-4 bg-amber-100 px-6 py-2 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200">
                        <span>メールアドレスが未確認です。</span>
                        <Link
                            href="/user/verify-email"
                            className="font-medium underline-offset-4 hover:underline"
                        >
                            確認する
                        </Link>
                    </div>
                )}

                <main className="flex-1 p-6">{children}</main>
            </div>
        </div>
    );
}
