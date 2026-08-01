import { FormEvent, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { KeyRound, ShieldCheck } from 'lucide-react';
import { toast } from 'sonner';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { loginWithPasskey } from '@/lib/passkeys';
import { type SharedProps } from '@/types';

interface LoginProps {
    strings: {
        title: string;
        email: string;
        password: string;
        remember: string;
        submit: string;
    };
}

export default function Login({ strings }: LoginProps) {
    const { csrf_token } = usePage<SharedProps>().props;
    const { data, setData, post, processing } = useForm({
        email: '',
        password: '',
        remember_me: false,
    });
    const [passkeyBusy, setPasskeyBusy] = useState(false);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/user/login');
    };

    const passkeyLogin = async () => {
        setPasskeyBusy(true);
        try {
            const redirect = await loginWithPasskey(csrf_token);
            router.visit(redirect);
        } catch (err) {
            toast.error(
                err instanceof Error
                    ? err.message
                    : 'Passkey login failed.',
            );
            setPasskeyBusy(false);
        }
    };

    return (
        <GuestLayout>
            <Head title={strings.title} />
            <Card>
                <CardContent className="pt-2">
                    <div className="mb-4 flex items-center justify-center gap-2">
                        <ShieldCheck className="size-6 text-primary" />
                        <h1 className="text-xl font-semibold">{strings.title}</h1>
                    </div>

                    <Separator className="mb-6" />

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">{strings.email}</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="mail@example.com"
                                required
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password">{strings.password}</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                required
                            />
                        </div>

                        <div className="flex items-center gap-2">
                            <Checkbox
                                id="remember_me"
                                checked={data.remember_me}
                                onCheckedChange={(checked) =>
                                    setData('remember_me', checked === true)
                                }
                            />
                            <Label htmlFor="remember_me">{strings.remember}</Label>
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            {strings.submit}
                        </Button>
                    </form>

                    <div className="mt-4 space-y-3">
                        <div className="flex items-center gap-3">
                            <Separator className="flex-1" />
                            <span className="text-xs text-muted-foreground">
                                or
                            </span>
                            <Separator className="flex-1" />
                        </div>
                        <Button
                            type="button"
                            variant="outline"
                            className="w-full"
                            onClick={passkeyLogin}
                            disabled={passkeyBusy}
                        >
                            <KeyRound className="size-4" />
                            Log in with a passkey
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
