import { FormEvent } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { UserPlus } from 'lucide-react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

export default function Register() {
    const { errors } = usePage<SharedProps>().props;
    const { data, setData, post, processing } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/auth/register');
    };

    return (
        <GuestLayout>
            <Head title="新規登録" />
            <Card>
                <CardContent className="pt-2">
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <UserPlus className="size-6 text-primary" />
                        <h1 className="text-xl font-semibold">新規登録</h1>
                    </div>

                    <Separator className="mb-6" />

                    <form onSubmit={submit} className="space-y-4">
                        <Field
                            id="name"
                            label="名前"
                            value={data.name}
                            error={errors.name}
                            onChange={(v) => setData('name', v)}
                        />
                        <Field
                            id="email"
                            type="email"
                            label="メールアドレス"
                            value={data.email}
                            error={errors.email}
                            onChange={(v) => setData('email', v)}
                        />
                        <Field
                            id="password"
                            type="password"
                            label="パスワード"
                            value={data.password}
                            error={errors.password}
                            onChange={(v) => setData('password', v)}
                        />
                        <Field
                            id="password_confirmation"
                            type="password"
                            label="パスワード（確認）"
                            value={data.password_confirmation}
                            error={errors.password_confirmation}
                            onChange={(v) => setData('password_confirmation', v)}
                        />

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            登録
                        </Button>

                        <p className="text-center text-sm text-muted-foreground">
                            すでにアカウントをお持ちですか？{' '}
                            <Link
                                href="/auth/login"
                                className="text-primary underline-offset-4 hover:underline"
                            >
                                ログイン
                            </Link>
                        </p>
                    </form>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}

interface FieldProps {
    id: string;
    label: string;
    value: string;
    error?: string;
    type?: string;
    onChange: (value: string) => void;
}

function Field({ id, label, value, error, type = 'text', onChange }: FieldProps) {
    return (
        <div className="space-y-2">
            <Label htmlFor={id}>{label}</Label>
            <Input
                id={id}
                type={type}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                required
            />
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}
