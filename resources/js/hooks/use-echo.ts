import { useEffect } from 'react';

import echo from '@/echo';

type ChannelKind = 'public' | 'private' | 'presence';

/**
 * Thin hook that subscribes to a channel and receives events, leaving on unmount.
 * Does nothing when Echo is uninitialised (no key configured).
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
