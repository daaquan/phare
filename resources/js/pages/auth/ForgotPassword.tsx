import { FormEvent } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedProps } from '@/types';

export default function ForgotPassword() {
    const { flash, errors } = usePage<SharedProps>().props;
    const { data, setData, post, processing } = useForm({ email: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/user/forgot-password');
    };

    return (
        <GuestLayout>
            <Head title="パスワードをお忘れですか" />
            <Card>
                <CardContent className="space-y-4 pt-6">
                    <h1 className="text-xl font-semibold">パスワード再設定</h1>
                    <p className="text-sm text-muted-foreground">
                        登録済みのメールアドレスに再設定用リンクを送信します。
                    </p>

                    {flash.success && (
                        <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                            {flash.success}
                        </p>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">メールアドレス</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                            />
                            {errors.email && (
                                <p className="text-sm text-destructive">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            リンクを送信
                        </Button>

                        <p className="text-center text-sm">
                            <Link
                                href="/user/login"
                                className="text-primary underline-offset-4 hover:underline"
                            >
                                ログインに戻る
                            </Link>
                        </p>
                    </form>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
