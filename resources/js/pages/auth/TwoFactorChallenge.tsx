import { FormEvent, useState } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

export default function TwoFactorChallenge() {
    const { errors } = usePage<SharedProps>().props;
    const [useRecovery, setUseRecovery] = useState(false);
    const { data, setData, post, processing } = useForm({
        code: '',
        recovery_code: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/user/two-factor-challenge');
    };

    return (
        <GuestLayout>
            <Head title="二段階認証" />
            <Card>
                <CardContent className="pt-2">
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <ShieldCheck className="size-6 text-primary" />
                        <h1 className="text-xl font-semibold">二段階認証</h1>
                    </div>

                    <Separator className="mb-6" />

                    <p className="mb-4 text-sm text-muted-foreground">
                        {useRecovery
                            ? 'リカバリーコードのいずれかを入力してください。'
                            : '認証アプリに表示されているコードを入力してください。'}
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        {!useRecovery ? (
                            <div className="space-y-2">
                                <Label htmlFor="code">認証コード</Label>
                                <Input
                                    id="code"
                                    inputMode="numeric"
                                    value={data.code}
                                    onChange={(e) =>
                                        setData('code', e.target.value)
                                    }
                                    placeholder="123456"
                                    autoFocus
                                />
                            </div>
                        ) : (
                            <div className="space-y-2">
                                <Label htmlFor="recovery_code">
                                    リカバリーコード
                                </Label>
                                <Input
                                    id="recovery_code"
                                    value={data.recovery_code}
                                    onChange={(e) =>
                                        setData('recovery_code', e.target.value)
                                    }
                                    autoFocus
                                />
                            </div>
                        )}

                        {errors.code && (
                            <p className="text-sm text-destructive">
                                {errors.code}
                            </p>
                        )}

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            ログイン
                        </Button>
                    </form>

                    <button
                        type="button"
                        onClick={() => setUseRecovery((v) => !v)}
                        className="mt-4 w-full text-center text-sm text-muted-foreground underline-offset-4 hover:underline"
                    >
                        {useRecovery
                            ? '認証コードを使う'
                            : 'リカバリーコードを使う'}
                    </button>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
