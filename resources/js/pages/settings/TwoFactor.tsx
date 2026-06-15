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
        <SettingsLayout title="Two-factor authentication">
            <Head title="Two-factor authentication" />

            {flash.success && (
                <p className="mb-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                    {flash.success}
                </p>
            )}

            <p className="mb-6 text-sm text-muted-foreground">
                When two-factor authentication is enabled, you must enter a code from an
                authenticator app (such as Google Authenticator) when logging in.
            </p>

            {/* Disabled: enable button */}
            {!enabled && !pending && (
                <Button onClick={enable} disabled={enableForm.processing}>
                    Enable two-factor authentication
                </Button>
            )}

            {/* Awaiting confirmation: manual key + code confirmation form */}
            {pending && (
                <div className="space-y-4">
                    <div className="rounded-md border p-4">
                        <p className="text-sm font-medium">Setup key</p>
                        <p className="mt-1 break-all font-mono text-sm">
                            {secret}
                        </p>
                        <p className="mt-2 text-xs text-muted-foreground">
                            Register the key above manually in your authenticator app, or scan the following URI
                            to set it up:
                        </p>
                        <p className="mt-1 break-all font-mono text-xs text-muted-foreground">
                            {otpauthUri}
                        </p>
                    </div>

                    <form onSubmit={confirm} className="space-y-2">
                        <Label htmlFor="code">Authentication code</Label>
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
                            Confirm and enable
                        </Button>
                    </form>
                </div>
            )}

            {/* Enabled: recovery codes + disable */}
            {enabled && (
                <div className="space-y-6">
                    <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                        Two-factor authentication is enabled.
                    </p>

                    <div>
                        <h3 className="text-base font-semibold">
                            Recovery codes
                        </h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Store these in a safe place in case you cannot use your authenticator app. Each code can be used only once.
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
                            Regenerate recovery codes
                        </Button>
                    </div>

                    <Separator />

                    <Button
                        variant="destructive"
                        onClick={disable}
                        disabled={disableForm.processing}
                    >
                        Disable two-factor authentication
                    </Button>
                </div>
            )}
        </SettingsLayout>
    );
}
