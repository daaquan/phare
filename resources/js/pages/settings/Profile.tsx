import { FormEvent } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';

import SettingsLayout from '@/layouts/SettingsLayout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedProps } from '@/types';

export default function Profile() {
    const { auth, flash, errors } = usePage<SharedProps>().props;
    const { data, setData, patch, processing } = useForm({
        name: auth.user?.name ?? '',
        email: auth.user?.email ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        patch('/settings/profile');
    };

    return (
        <SettingsLayout title="Profile">
            <Head title="Profile settings" />

            {flash.success && (
                <p className="mb-4 rounded-md bg-primary/10 px-3 py-2 text-sm text-primary">
                    {flash.success}
                </p>
            )}

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

                <Button type="submit" disabled={processing}>
                    Save
                </Button>
            </form>
        </SettingsLayout>
    );
}
