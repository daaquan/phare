import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import ManagePasskeys from '@/components/manage-passkeys';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { type Passkey, type SharedProps } from '@/types';

interface TwoFactorState {
    enabled: boolean;
    pending: boolean;
    secret?: string;
    otpauthUri?: string;
    recoveryCodes?: string[];
}

interface SecurityProps {
    twoFactor: TwoFactorState;
    passkeys: Passkey[];
}

export default function Security() {
    const { errors, twoFactor, passkeys } =
        usePage<SharedProps & SecurityProps>().props;

    // パスワード変更。
    const password = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });
    const submitPassword = (e: FormEvent) => {
        e.preventDefault();
        password.put('/settings/password', {
            onSuccess: () => password.reset(),
        });
    };

    // 二段階認証。
    const enableForm = useForm({});
    const confirmForm = useForm({ code: '' });
    const recoveryForm = useForm({});
    const disableForm = useForm({});

    const confirm = (e: FormEvent) => {
        e.preventDefault();
        confirmForm.post('/settings/two-factor/confirm');
    };

    return (
        <SettingsLayout title="セキュリティ">
            <Head title="セキュリティ設定" />

            <div className="space-y-10">
                {/* パスワード変更 */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">パスワード</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            安全のため、長くランダムなパスワードを使用してください。
                        </p>
                    </div>

                    <form onSubmit={submitPassword} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="current_password">現在のパスワード</Label>
                            <Input
                                id="current_password"
                                type="password"
                                value={password.data.current_password}
                                onChange={(e) =>
                                    password.setData('current_password', e.target.value)
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
                                value={password.data.password}
                                onChange={(e) =>
                                    password.setData('password', e.target.value)
                                }
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
                                value={password.data.password_confirmation}
                                onChange={(e) =>
                                    password.setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                            />
                        </div>

                        <Button type="submit" disabled={password.processing}>
                            パスワードを更新
                        </Button>
                    </form>
                </section>

                <Separator />

                {/* 二段階認証 */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">二段階認証</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            ログイン時に認証アプリ（Google Authenticator
                            など）のコード入力を必須にします。
                        </p>
                    </div>

                    {/* 無効状態 */}
                    {!twoFactor.enabled && !twoFactor.pending && (
                        <Button
                            onClick={() => enableForm.post('/settings/two-factor/enable')}
                            disabled={enableForm.processing}
                        >
                            二段階認証を有効にする
                        </Button>
                    )}

                    {/* 確認待ち: セットアップキー + コード確認 */}
                    {twoFactor.pending && (
                        <div className="space-y-4">
                            <div className="rounded-md border p-4">
                                <p className="text-sm font-medium">セットアップキー</p>
                                <p className="mt-1 font-mono text-sm break-all">
                                    {twoFactor.secret}
                                </p>
                                <p className="mt-2 text-xs text-muted-foreground">
                                    認証アプリに上記キーを手動で登録するか、次の URI
                                    を読み込んでください:
                                </p>
                                <p className="mt-1 font-mono text-xs break-all text-muted-foreground">
                                    {twoFactor.otpauthUri}
                                </p>
                            </div>

                            <form onSubmit={confirm} className="space-y-3">
                                <Label>認証コード</Label>
                                <InputOTP
                                    maxLength={6}
                                    value={confirmForm.data.code}
                                    onChange={(value) =>
                                        confirmForm.setData('code', value)
                                    }
                                >
                                    <InputOTPGroup>
                                        {Array.from({ length: 6 }).map((_, i) => (
                                            <InputOTPSlot key={i} index={i} />
                                        ))}
                                    </InputOTPGroup>
                                </InputOTP>
                                {errors.code && (
                                    <p className="text-sm text-destructive">
                                        {errors.code}
                                    </p>
                                )}
                                <Button
                                    type="submit"
                                    disabled={confirmForm.processing}
                                >
                                    確認して有効化
                                </Button>
                            </form>
                        </div>
                    )}

                    {/* 有効状態: リカバリーコード + 無効化 */}
                    {twoFactor.enabled && (
                        <div className="space-y-6">
                            <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                                二段階認証は有効です。
                            </p>

                            <div>
                                <h4 className="text-sm font-semibold">
                                    リカバリーコード
                                </h4>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    認証アプリを使えない場合に備え、安全な場所に保管してください。各コードは一度だけ使用できます。
                                </p>
                                <ul className="mt-3 grid grid-cols-2 gap-2 rounded-md bg-muted p-4 font-mono text-sm">
                                    {(twoFactor.recoveryCodes ?? []).map((code) => (
                                        <li key={code}>{code}</li>
                                    ))}
                                </ul>
                                <Button
                                    variant="outline"
                                    className="mt-3"
                                    onClick={() =>
                                        recoveryForm.post(
                                            '/settings/two-factor/recovery-codes',
                                        )
                                    }
                                    disabled={recoveryForm.processing}
                                >
                                    リカバリーコードを再生成
                                </Button>
                            </div>

                            <Button
                                variant="destructive"
                                onClick={() =>
                                    disableForm.delete('/settings/two-factor')
                                }
                                disabled={disableForm.processing}
                            >
                                二段階認証を無効にする
                            </Button>
                        </div>
                    )}
                </section>

                <Separator />

                {/* パスキー */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">パスキー</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            指紋・顔認証・端末のPINでパスワードなしにログインできます。
                        </p>
                    </div>

                    <ManagePasskeys passkeys={passkeys} />
                </section>
            </div>
        </SettingsLayout>
    );
}
