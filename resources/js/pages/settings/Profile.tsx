import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

export default function Profile() {
    const { auth, flash, errors } = usePage<SharedProps>().props;
    const { data, setData, patch, processing } = useForm({
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch('/settings/profile');
    };

    // 認証メール再送（未認証ユーザー向け）。
    const resend = useForm({});
    const resendVerification = () =>
        resend.post('/user/email/verification-notification');

    // アカウント削除（パスワード再入力を要求）。
    const remove = useForm({ password: '' });
    const deleteAccount = (e: FormEvent) => {
        e.preventDefault();
        remove.delete('/settings/profile');
    };

    return (
        <SettingsLayout title="プロフィール">
            <Head title="プロフィール設定" />

            {flash.success && (
                <p className="mb-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                    {flash.success}
                </p>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="name">名前</Label>
                    <Input
                        id="name"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                    />
                    {errors.name && (
                        <p className="text-sm text-destructive">{errors.name}</p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="email">メールアドレス</Label>
                    <Input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                    />
                    {errors.email && (
                        <p className="text-sm text-destructive">{errors.email}</p>
                    )}
                </div>

                {auth.user && !auth.user.email_verified && (
                    <div className="rounded-md bg-amber-500/10 px-3 py-2 text-sm">
                        <p className="text-amber-700 dark:text-amber-400">
                            メールアドレスが未確認です。
                        </p>
                        <button
                            type="button"
                            onClick={resendVerification}
                            disabled={resend.processing}
                            className="mt-1 font-medium text-primary underline-offset-4 hover:underline"
                        >
                            確認メールを再送する
                        </button>
                    </div>
                )}

                <Button type="submit" disabled={processing}>
                    保存
                </Button>
            </form>

            <Separator className="my-8" />

            <section className="space-y-4">
                <div>
                    <h3 className="text-base font-semibold text-destructive">
                        アカウントの削除
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        アカウントを削除すると、すべてのデータが完全に失われます。この操作は取り消せません。
                    </p>
                </div>

                <form onSubmit={deleteAccount} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="delete_password">パスワード</Label>
                        <Input
                            id="delete_password"
                            type="password"
                            value={remove.data.password}
                            onChange={(e) =>
                                remove.setData('password', e.target.value)
                            }
                            placeholder="確認のためパスワードを入力"
                        />
                        {errors.password && (
                            <p className="text-sm text-destructive">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    <Button
                        type="submit"
                        variant="destructive"
                        disabled={remove.processing}
                    >
                        アカウントを削除
                    </Button>
                </form>
            </section>
        </SettingsLayout>
    );
}
