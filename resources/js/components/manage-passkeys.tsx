import { FormEvent, useState } from 'react';
import { router, usePage } from '@inertiajs/react';
import { KeyRound, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { deletePasskey, registerPasskey } from '@/lib/passkeys';
import { type Passkey, type SharedProps } from '@/types';

interface Props {
    passkeys: Passkey[];
}

export default function ManagePasskeys({ passkeys }: Props) {
    const { csrf_token } = usePage<SharedProps>().props;
    const [name, setName] = useState('');
    const [busy, setBusy] = useState(false);

    const refresh = () => router.reload({ only: ['passkeys', 'flash'] });

    const register = async (e: FormEvent) => {
        e.preventDefault();
        setBusy(true);
        try {
            await registerPasskey(name.trim(), csrf_token);
            setName('');
            refresh();
        } catch (err) {
            toast.error(
                err instanceof Error ? err.message : 'Passkey registration failed.',
            );
        } finally {
            setBusy(false);
        }
    };

    const remove = async (id: number) => {
        setBusy(true);
        try {
            await deletePasskey(id, csrf_token);
            refresh();
        } catch (err) {
            toast.error(err instanceof Error ? err.message : 'Delete failed.');
        } finally {
            setBusy(false);
        }
    };

    return (
        <div className="space-y-4">
            {passkeys.length > 0 ? (
                <ul className="divide-y rounded-md border">
                    {passkeys.map((pk) => (
                        <li
                            key={pk.id}
                            className="flex items-center justify-between gap-3 px-4 py-3"
                        >
                            <div className="flex items-center gap-3">
                                <KeyRound className="size-4 text-muted-foreground" />
                                <div>
                                    <p className="text-sm font-medium">{pk.name}</p>
                                    {pk.last_used_at && (
                                        <p className="text-xs text-muted-foreground">
                                            Last used: {pk.last_used_at}
                                        </p>
                                    )}
                                </div>
                            </div>
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => remove(pk.id)}
                                disabled={busy}
                                aria-label="Delete passkey"
                            >
                                <Trash2 className="size-4 text-destructive" />
                            </Button>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-sm text-muted-foreground">
                    No passkeys registered yet.
                </p>
            )}

            <form onSubmit={register} className="flex items-end gap-2">
                <div className="flex-1 space-y-2">
                    <Label htmlFor="passkey_name">Passkey name (optional)</Label>
                    <Input
                        id="passkey_name"
                        value={name}
                        onChange={(e) => setName(e.target.value)}
                        placeholder="e.g. MacBook Touch ID"
                    />
                </div>
                <Button type="submit" disabled={busy}>
                    <KeyRound className="size-4" />
                    Add passkey
                </Button>
            </form>
        </div>
    );
}
