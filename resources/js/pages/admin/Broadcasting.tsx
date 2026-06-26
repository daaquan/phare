import { Head, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { useEcho } from '@/hooks/use-echo';
import AppLayout from '@/layouts/AppLayout';
import type { SharedProps } from '@/types';

interface ChannelRow {
    name: string;
    type: string;
    subscription_count: number | null;
}

interface ConnectionMeta {
    driver: string | null;
    host: string | null;
    port: number | null;
    scheme: string | null;
}

interface LiveMessage {
    user_id: number;
    message: string;
    at: string;
}

interface Props {
    connection: ConnectionMeta;
}

const POLL_MS = 5000;

export default function Broadcasting({ connection }: Props) {
    const { csrf_token } = usePage<SharedProps>().props;

    const [channels, setChannels] = useState<ChannelRow[]>([]);
    const [reachable, setReachable] = useState<boolean | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [message, setMessage] = useState('test broadcast');
    const [sending, setSending] = useState(false);
    const [live, setLive] = useState<LiveMessage[]>([]);

    const loadChannels = useCallback(async () => {
        try {
            const res = await fetch('/admin/broadcasting/channels', {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const data = await res.json();
            setReachable(Boolean(data.ok));
            setError(data.ok ? null : (data.error ?? 'unreachable'));
            setChannels(Array.isArray(data.channels) ? data.channels : []);
        } catch (e) {
            setReachable(false);
            setError(e instanceof Error ? e.message : 'fetch failed');
        }
    }, []);

    useEffect(() => {
        loadChannels();
        const id = setInterval(loadChannels, POLL_MS);
        return () => clearInterval(id);
    }, [loadChannels]);

    // ライブ受信: presence-monitor に届くテスト送信を表示し、Echo 経路を実証。
    useEcho(
        'monitor',
        '.message',
        (payload) => setLive((prev) => [payload as LiveMessage, ...prev].slice(0, 20)),
        'presence',
    );

    const sendTest = async () => {
        setSending(true);
        try {
            const res = await fetch('/admin/broadcasting/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf_token,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message }),
            });
            const data = await res.json();
            if (!data.ok) {
                setError(data.error ?? 'send failed');
            }
        } catch (e) {
            setError(e instanceof Error ? e.message : 'send failed');
        } finally {
            setSending(false);
        }
    };

    return (
        <AppLayout title="Broadcasting Monitor">
            <Head title="Broadcasting Monitor" />

            <div className="mb-6 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                <span>
                    driver: <code>{connection.driver}</code>
                </span>
                <span>
                    soketi: <code>{connection.scheme}://{connection.host}:{connection.port}</code>
                </span>
                <span
                    className={
                        reachable === null
                            ? 'text-muted-foreground'
                            : reachable
                              ? 'text-green-600'
                              : 'text-red-600'
                    }
                >
                    {reachable === null
                        ? '接続確認中…'
                        : reachable
                          ? '● 接続OK'
                          : '● 接続不可'}
                </span>
            </div>

            {error && !reachable && (
                <Card className="mb-6 border-red-300">
                    <CardContent className="py-3 text-sm text-red-600">
                        Soketi に接続できません: {error}
                        <br />
                        <span className="text-muted-foreground">
                            `npx @soketi/soketi start` を起動し、PUSHER_* env を確認してください。
                        </span>
                    </CardContent>
                </Card>
            )}

            <div className="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>占有チャンネル</CardTitle>
                        <CardDescription>
                            {POLL_MS / 1000}s ごとに自動更新
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b text-left text-muted-foreground">
                                    <th className="py-2 pr-4">チャンネル</th>
                                    <th className="py-2 pr-4">種別</th>
                                    <th className="py-2">購読数</th>
                                </tr>
                            </thead>
                            <tbody>
                                {channels.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan={3}
                                            className="py-4 text-muted-foreground"
                                        >
                                            占有中のチャンネルはありません
                                        </td>
                                    </tr>
                                )}
                                {channels.map((c) => (
                                    <tr
                                        key={c.name}
                                        className="border-b last:border-0"
                                    >
                                        <td className="py-2 pr-4 font-medium">
                                            {c.name}
                                        </td>
                                        <td className="py-2 pr-4">{c.type}</td>
                                        <td className="py-2">
                                            {c.subscription_count ?? '—'}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>テスト送信 + ライブ受信</CardTitle>
                        <CardDescription>
                            presence-monitor 経由で配信を実証
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="flex gap-2">
                            <Input
                                value={message}
                                onChange={(e) => setMessage(e.target.value)}
                                placeholder="メッセージ"
                            />
                            <Button onClick={sendTest} disabled={sending}>
                                {sending ? '送信中…' : '送信'}
                            </Button>
                        </div>

                        <ul className="space-y-1 text-sm">
                            {live.length === 0 && (
                                <li className="text-muted-foreground">
                                    受信待ち…（送信するとここに表示）
                                </li>
                            )}
                            {live.map((m, i) => (
                                <li
                                    key={`${m.at}-${i}`}
                                    className="rounded bg-muted px-2 py-1"
                                >
                                    <span className="text-muted-foreground">
                                        {m.at}
                                    </span>{' '}
                                    user#{m.user_id}: {m.message}
                                </li>
                            ))}
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
