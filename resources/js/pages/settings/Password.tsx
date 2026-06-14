import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedProps } from '@/types';

export default function Password() {
    const { flash, errors } = usePage<SharedProps>().props;
    const { data, setData, put, processing, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        put('/settings/password', {
            onSuccess: () => reset(),
        });
    };

    return (
        <SettingsLayout title="パスワード">
            <Head title="パスワード設定" />

            {flash.success && (
                <p className="mb-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                    {flash.success}
                </p>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="current_password">現在のパスワード</Label>
                    <Input
                        id="current_password"
                        type="password"
                        value={data.current_password}
                        onChange={(e) =>
                            setData('current_password', e.target.value)
                        }
                    />
                    {errors.current_password && (
                        <p className="text-sm text-destructive">
                            {errors.current_password}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password">新しいパスワード</Label>
                    <Input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                    />
                    {errors.password && (
                        <p className="text-sm text-destructive">
                            {errors.password}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="password_confirmation">
                        新しいパスワード（確認）
                    </Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) =>
                            setData('password_confirmation', e.target.value)
                        }
                    />
                </div>

                <Button type="submit" disabled={processing}>
                    更新
                </Button>
            </form>
        </SettingsLayout>
    );
}
