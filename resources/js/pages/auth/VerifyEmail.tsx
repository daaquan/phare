import { FormEvent } from 'react';
import { Head, router, useForm } from '@inertiajs/react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

export default function VerifyEmail() {
    const { post, processing } = useForm({});

    const resend = (e: FormEvent) => {
        e.preventDefault();
        post('/user/email/verification-notification');
    };

    return (
        <GuestLayout>
            <Head title="メール認証" />
            <Card>
                <CardContent className="space-y-4 pt-6">
                    <h1 className="text-xl font-semibold">メールアドレスの確認</h1>
                    <p className="text-sm text-muted-foreground">
                        登録時に送信した確認メールのリンクをクリックしてください。
                        メールが届かない場合は再送できます。
                    </p>

                    <div className="flex items-center justify-between">
                        <form onSubmit={resend}>
                            <Button type="submit" disabled={processing}>
                                認証メールを再送
                            </Button>
                        </form>
                        <Button
                            variant="ghost"
                            onClick={() => router.post('/user/logout')}
                        >
                            ログアウト
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
