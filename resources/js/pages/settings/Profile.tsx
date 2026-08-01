import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { type SharedProps } from '@/types';

export default function Profile() {
    const { auth, errors } = usePage<SharedProps>().props;
    const { data, setData, patch, processing } = useForm({
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch('/settings/profile');
    };

    // Resend the verification email (for unverified users).
    const resend = useForm({});
    const resendVerification = () =>
        resend.post('/user/email/verification-notification');

    // Delete the account, re-asking for the password.
    const remove = useForm({ password: '' });
    const deleteAccount = (e: FormEvent) => {
        e.preventDefault();
        remove.delete('/settings/profile');
    };

    return (
        <SettingsLayout title="Profile">
            <Head title="Profile settings" />

            <form onSubmit={submit} className="space-y-4">
                <div className="space-y-2">
                    <Label htmlFor="name">Name</Label>
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
                    <Label htmlFor="email">Email address</Label>
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
                            Your email address is unverified.
                        </p>
                        <button
                            type="button"
                            onClick={resendVerification}
                            disabled={resend.processing}
                            className="mt-1 font-medium text-primary underline-offset-4 hover:underline"
                        >
                            Resend the verification email
                        </button>
                    </div>
                )}

                <Button type="submit" disabled={processing}>
                    Save
                </Button>
            </form>

            <Separator className="my-8" />

            <section className="space-y-4">
                <div>
                    <h3 className="text-base font-semibold text-destructive">
                        Delete account
                    </h3>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Deleting your account permanently destroys all of its data. This cannot be undone.
                    </p>
                </div>

                <form onSubmit={deleteAccount} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="delete_password">Password</Label>
                        <Input
                            id="delete_password"
                            type="password"
                            value={remove.data.password}
                            onChange={(e) =>
                                remove.setData('password', e.target.value)
                            }
                            placeholder="Enter your password to confirm"
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
                        Delete account
                    </Button>
                </form>
            </section>
        </SettingsLayout>
    );
}
