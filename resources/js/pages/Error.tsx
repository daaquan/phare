import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';

interface ErrorProps {
    status: number;
}

const messages: Record<number, { title: string; description: string }> = {
    403: { title: '403', description: 'Access to this page is forbidden.' },
    404: { title: '404', description: 'That page could not be found.' },
    419: { title: '419', description: 'The page has expired.' },
    429: { title: '429', description: 'Too many requests.' },
    500: { title: '500', description: 'A server error occurred.' },
    503: { title: '503', description: 'Down for maintenance.' },
};

export default function ErrorPage({ status }: ErrorProps) {
    const { title, description } = messages[status] ?? {
        title: String(status),
        description: 'Something went wrong.',
    };

    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-background px-6 text-center text-foreground">
                <h1 className="text-6xl font-bold">{title}</h1>
                <p className="text-muted-foreground">{description}</p>
                <Button asChild>
                    <Link href="/">Back to home</Link>
                </Button>
            </div>
        </>
    );
}
