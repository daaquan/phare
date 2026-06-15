import { Head, Link, usePage } from '@inertiajs/react';
import { BookOpen, Zap, GitBranch } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { type SharedProps } from '@/types';

interface Card {
    title: string;
    description: string;
    href: string;
    icon: 'docs' | 'phalcon' | 'github';
}

interface WelcomeProps {
    title: string;
    strings: {
        login: string;
        dashboard: string;
        footer: string;
    };
    cards: Card[];
}

const icons = {
    docs: BookOpen,
    phalcon: Zap,
    github: GitBranch,
} as const;

export default function Welcome({ title, strings, cards }: WelcomeProps) {
    const { auth } = usePage<SharedProps>().props;

    return (
        <>
            <Head title={title} />
            <div className="min-h-screen bg-background text-foreground">
                <div className="mx-auto w-full max-w-7xl px-6 py-10">
                    <header className="flex items-center justify-between py-10">
                        <span className="text-xl font-semibold">Phare</span>
                        {auth.user ? (
                            <Button asChild>
                                <Link href="/dashboard">{strings.dashboard}</Link>
                            </Button>
                        ) : (
                            <Button asChild>
                                <Link href="/user/login">{strings.login}</Link>
                            </Button>
                        )}
                    </header>

                    <main className="mt-6 grid gap-6 lg:grid-cols-3">
                        {cards.map((card) => {
                            const Icon = icons[card.icon];
                            return (
                                <a
                                    key={card.href}
                                    href={card.href}
                                    className="flex flex-col gap-6 rounded-xl border bg-card p-8 text-card-foreground shadow-sm transition hover:shadow-md"
                                >
                                    <div className="flex size-12 items-center justify-center rounded-full bg-primary/10">
                                        <Icon className="size-6 text-primary" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-semibold">
                                            {card.title}
                                        </h3>
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            {card.description}
                                        </p>
                                    </div>
                                </a>
                            );
                        })}
                    </main>

                    <footer className="py-16 text-center text-sm text-muted-foreground">
                        {strings.footer}
                    </footer>
                </div>
            </div>
        </>
    );
}
