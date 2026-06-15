import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { Lock } from 'lucide-react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

export default function ConfirmPassword() {
    const { errors } = usePage<SharedProps>().props;
    const { data, setData, post, processing } = useForm({ password: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/user/confirm-password');
    };

    return (
        <GuestLayout>
            <Head title="パスワードの確認" />
            <Card>
                <CardContent className="pt-2">
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <Lock className="size-6 text-primary" />
                        <h1 className="text-xl font-semibold">
                            パスワードの確認
                        </h1>
                    </div>

                    <Separator className="mb-6" />

                    <p className="mb-4 text-sm text-muted-foreground">
                        これは保護された領域です。続行するにはパスワードを再入力してください。
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="password">パスワード</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                autoFocus
                                required
                            />
                            {errors.password && (
                                <p className="text-sm text-destructive">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            確認
                        </Button>
                    </form>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
