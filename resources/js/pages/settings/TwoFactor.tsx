import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

interface TwoFactorProps {
    enabled: boolean;
    pending: boolean;
    secret?: string;
    otpauthUri?: string;
    recoveryCodes?: string[];
}

export default function TwoFactor() {
    const page = usePage<SharedProps & TwoFactorProps>().props;
    const { flash, errors, enabled, pending, secret, otpauthUri, recoveryCodes } =
        page;

    const enableForm = useForm({});
    const confirmForm = useForm({ code: '' });
    const recoveryForm = useForm({});
    const disableForm = useForm({});

    const enable = () => enableForm.post('/settings/two-factor/enable');
    const confirm = (e: FormEvent) => {
        e.preventDefault();
        confirmForm.post('/settings/two-factor/confirm');
    };
    const regenerate = () =>
        recoveryForm.post('/settings/two-factor/recovery-codes');
    const disable = () => disableForm.delete('/settings/two-factor');

    return (
        <SettingsLayout title="二段階認証">
            <Head title="二段階認証" />

            {flash.success && (
                <p className="mb-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                    {flash.success}
                </p>
            )}

            <p className="mb-6 text-sm text-muted-foreground">
                二段階認証を有効にすると、ログイン時に認証アプリ（Google
                Authenticator など）のコード入力が必要になります。
            </p>

            {/* 無効状態: 有効化ボタン */}
            {!enabled && !pending && (
                <Button onClick={enable} disabled={enableForm.processing}>
                    二段階認証を有効にする
                </Button>
            )}

            {/* 確認待ち: 手入力キー + コード確認フォーム */}
            {pending && (
                <div className="space-y-4">
                    <div className="rounded-md border p-4">
                        <p className="text-sm font-medium">セットアップキー</p>
                        <p className="mt-1 break-all font-mono text-sm">
                            {secret}
                        </p>
                        <p className="mt-2 text-xs text-muted-foreground">
                            認証アプリに上記キーを手動で登録するか、次の URI
                            を読み込んでください:
                        </p>
                        <p className="mt-1 break-all font-mono text-xs text-muted-foreground">
                            {otpauthUri}
                        </p>
                    </div>

                    <form onSubmit={confirm} className="space-y-2">
                        <Label htmlFor="code">認証コード</Label>
                        <Input
                            id="code"
                            inputMode="numeric"
                            value={confirmForm.data.code}
                            onChange={(e) =>
                                confirmForm.setData('code', e.target.value)
                            }
                            placeholder="123456"
                            autoFocus
                        />
                        {errors.code && (
                            <p className="text-sm text-destructive">
                                {errors.code}
                            </p>
                        )}
                        <Button type="submit" disabled={confirmForm.processing}>
                            確認して有効化
                        </Button>
                    </form>
                </div>
            )}

            {/* 有効状態: リカバリーコード + 無効化 */}
            {enabled && (
                <div className="space-y-6">
                    <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                        二段階認証は有効です。
                    </p>

                    <div>
                        <h3 className="text-base font-semibold">
                            リカバリーコード
                        </h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            認証アプリを使えない場合に備え、安全な場所に保管してください。各コードは一度だけ使用できます。
                        </p>
                        <ul className="mt-3 grid grid-cols-2 gap-2 rounded-md bg-muted p-4 font-mono text-sm">
                            {(recoveryCodes ?? []).map((code) => (
                                <li key={code}>{code}</li>
                            ))}
                        </ul>
                        <Button
                            variant="outline"
                            className="mt-3"
                            onClick={regenerate}
                            disabled={recoveryForm.processing}
                        >
                            リカバリーコードを再生成
                        </Button>
                    </div>

                    <Separator />

                    <Button
                        variant="destructive"
                        onClick={disable}
                        disabled={disableForm.processing}
                    >
                        二段階認証を無効にする
                    </Button>
                </div>
            )}
        </SettingsLayout>
    );
}
