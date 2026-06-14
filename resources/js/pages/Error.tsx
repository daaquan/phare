import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';

interface ErrorProps {
    status: number;
}

const messages: Record<number, { title: string; description: string }> = {
    403: { title: '403', description: 'このページへのアクセスは禁止されています。' },
    404: { title: '404', description: 'ページが見つかりませんでした。' },
    419: { title: '419', description: 'ページの有効期限が切れました。' },
    429: { title: '429', description: 'リクエストが多すぎます。' },
    500: { title: '500', description: 'サーバーエラーが発生しました。' },
    503: { title: '503', description: 'メンテナンス中です。' },
};

export default function ErrorPage({ status }: ErrorProps) {
    const { title, description } = messages[status] ?? {
        title: String(status),
        description: 'エラーが発生しました。',
    };

    return (
        <>
            <Head title={title} />
            <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-background px-6 text-center text-foreground">
                <h1 className="text-6xl font-bold">{title}</h1>
                <p className="text-muted-foreground">{description}</p>
                <Button asChild>
                    <Link href="/">ホームへ戻る</Link>
                </Button>
            </div>
        </>
    );
}
