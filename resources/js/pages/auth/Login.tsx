import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';

import GuestLayout from '@/layouts/GuestLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
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
    const { flash } = usePage<SharedProps>().props;
    const { data, setData, post, processing } = useForm({
        email: '',
        password: '',
        remember_me: false,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post('/auth/login');
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

                    {flash.error && (
                        <p className="mb-4 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                            {flash.error}
                        </p>
                    )}

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
                </CardContent>
            </Card>
        </GuestLayout>
    );
}
