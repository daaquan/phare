import { useEffect } from 'react';

import echo from '@/echo';

type ChannelKind = 'public' | 'private' | 'presence';

/**
 * チャンネルを購読しイベントを受信する薄いフック。アンマウント時に解除。
 * Echo 未初期化（鍵未設定）なら何もしない。
 */
export function useEcho(
    channel: string,
    event: string,
    callback: (payload: unknown) => void,
    kind: ChannelKind = 'private',
) {
    useEffect(() => {
        if (!echo) {
            return;
        }

        const sub =
            kind === 'presence'
                ? echo.join(channel)
                : kind === 'private'
                  ? echo.private(channel)
                  : echo.channel(channel);

        sub.listen(event, callback);

        return () => {
            echo?.leave(channel);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [channel, event, kind]);
}
