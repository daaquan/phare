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

    // Password change.
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

    // Two-factor authentication.
    const enableForm = useForm({});
    const confirmForm = useForm({ code: '' });
    const recoveryForm = useForm({});
    const disableForm = useForm({});

    const confirm = (e: FormEvent) => {
        e.preventDefault();
        confirmForm.post('/settings/two-factor/confirm');
    };

    return (
        <SettingsLayout title="Security">
            <Head title="Security settings" />

            <div className="space-y-10">
                {/* Password change */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">Password</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Use a long, random password to stay secure.
                        </p>
                    </div>

                    <form onSubmit={submitPassword} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="current_password">Current password</Label>
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
                            <Label htmlFor="password">New password</Label>
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
                                New password (confirm)
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
                            Update password
                        </Button>
                    </form>
                </section>

                <Separator />

                {/* Two-factor authentication */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">Two-factor authentication</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Require a code from an authenticator app (Google Authenticator and
                            the like) at login.
                        </p>
                    </div>

                    {/* Disabled */}
                    {!twoFactor.enabled && !twoFactor.pending && (
                        <Button
                            onClick={() => enableForm.post('/settings/two-factor/enable')}
                            disabled={enableForm.processing}
                        >
                            Enable two-factor authentication
                        </Button>
                    )}

                    {/* Awaiting confirmation: setup key + code check */}
                    {twoFactor.pending && (
                        <div className="space-y-4">
                            <div className="rounded-md border p-4">
                                <p className="text-sm font-medium">Setup key</p>
                                <p className="mt-1 font-mono text-sm break-all">
                                    {twoFactor.secret}
                                </p>
                                <p className="mt-2 text-xs text-muted-foreground">
                                    Add the key above to your authenticator manually, or load this URI
                                    instead:
                                </p>
                                <p className="mt-1 font-mono text-xs break-all text-muted-foreground">
                                    {twoFactor.otpauthUri}
                                </p>
                            </div>

                            <form onSubmit={confirm} className="space-y-3">
                                <Label>Authentication code</Label>
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
                                    Confirm and enable
                                </Button>
                            </form>
                        </div>
                    )}

                    {/* Enabled: recovery codes + disable */}
                    {twoFactor.enabled && (
                        <div className="space-y-6">
                            <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                                Two-factor authentication is enabled.
                            </p>

                            <div>
                                <h4 className="text-sm font-semibold">
                                    Recovery codes
                                </h4>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Keep these somewhere safe in case you lose your authenticator. Each code works once.
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
                                    Regenerate recovery codes
                                </Button>
                            </div>

                            <Button
                                variant="destructive"
                                onClick={() =>
                                    disableForm.delete('/settings/two-factor')
                                }
                                disabled={disableForm.processing}
                            >
                                Disable two-factor authentication
                            </Button>
                        </div>
                    )}
                </section>

                <Separator />

                {/* Passkeys */}
                <section className="space-y-4">
                    <div>
                        <h3 className="text-base font-semibold">Passkeys</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Log in without a password using a fingerprint, face or device PIN.
                        </p>
                    </div>

                    <ManagePasskeys passkeys={passkeys} />
                </section>
            </div>
        </SettingsLayout>
    );
}
