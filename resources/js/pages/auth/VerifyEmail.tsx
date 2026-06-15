import { FormEvent } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { type SharedProps } from '@/types';

export default function VerifyEmail() {
    const { flash } = usePage<SharedProps>().props;
    const { post, processing } = useForm({});

    const resend = (e: FormEvent) => {
        e.preventDefault();
        post('/user/email/verification-notification');
    };

    return (
        <GuestLayout>
            <Head title="Email verification" />
            <Card>
                <CardContent className="space-y-4 pt-6">
                    <h1 className="text-xl font-semibold">Verify your email address</h1>
                    <p className="text-sm text-muted-foreground">
                        Click the link in the verification email sent when you registered.
                        If it never arrived, you can have it resent.
                    </p>

                    {flash.success && (
                        <p className="rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                            {flash.success}
                        </p>
                    )}

                    <div className="flex items-center justify-between">
                        <form onSubmit={resend}>
                            <Button type="submit" disabled={processing}>
                                Resend verification email
                            </Button>
                        </form>
                        <Button
                            variant="ghost"
                            onClick={() => router.post('/user/logout')}
                        >
                            Log out
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
