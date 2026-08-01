<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageBroadcast;
use App\Http\Controllers\Controller;
use App\Models\User;
use Phare\Attributes\Route;
use Phare\Broadcasting\Broadcasters\PusherBroadcaster;
use Phare\Http\Request;
use Phare\Support\Facades\Auth;
use Phare\Support\Facades\Broadcast;
use Phare\Support\Facades\Inertia;
use Phare\Support\Facades\Log;
use Pusher\Pusher;

/**
 * Broadcasting monitor dashboard. Calls the Soketi (Pusher protocol) HTTP API
 * through the Pusher SDK and returns occupied channels, subscription counts and
 * presence members.
 *
 * The dashboard stays up when Soketi is down: empty results plus a warning log.
 */
class BroadcastingController extends Controller
{
    #[Route('broadcasting', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting')]
    public function index(Request $request)
    {
        return Inertia::render('admin/Broadcasting', [
            'connection' => $this->connectionMeta(),
        ]);
    }

    /**
     * Occupied channels, with their subscription counts.
     */
    #[Route('broadcasting/channels', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.channels')]
    public function channels(Request $request)
    {
        $prefix = (string)($request->get('prefix') ?? '');

        try {
            $pusher = $this->pusher();
            $params = ['info' => 'subscription_count'];
            if ($prefix !== '') {
                $params['filter_by_prefix'] = $prefix;
            }
            $result = $pusher->getChannels($params);

            $channels = [];
            foreach ((array)($result->channels ?? []) as $name => $info) {
                $channels[] = [
                    'name' => $name,
                    'type' => $this->channelType($name),
                    'subscription_count' => $info->subscription_count ?? null,
                ];
            }

            return $this->json(['ok' => true, 'channels' => $channels]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: getChannels failed', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'channels' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * One channel in detail: occupancy, subscription count, and members for presence.
     * Channel names contain dots, so they arrive as the ?name= query parameter.
     */
    #[Route('broadcasting/channel', middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.channel')]
    public function channel(Request $request)
    {
        $name = (string)($request->get('name') ?? '');
        if ($name === '') {
            return $this->json(['ok' => false, 'error' => 'name required'], 422);
        }

        try {
            $pusher = $this->pusher();

            $info = $pusher->getChannelInfo($name, ['info' => 'subscription_count,user_count']);
            $payload = [
                'name' => $name,
                'type' => $this->channelType($name),
                'occupied' => $info->occupied ?? false,
                'subscription_count' => $info->subscription_count ?? null,
                'user_count' => $info->user_count ?? null,
                'members' => [],
            ];

            if (str_starts_with($name, 'presence-')) {
                $users = $pusher->getPresenceUsers($name);
                $payload['members'] = array_map(
                    fn ($u) => ['id' => $u->id ?? null],
                    (array)($users->users ?? [])
                );
            }

            return $this->json(['ok' => true, 'channel' => $payload]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: getChannelInfo failed', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Test broadcast, sent to the current admin's private channel and presence-monitor.
     */
    #[Route('broadcasting/test', methods: ['POST'], middlewares: ['auth', 'verified', 'admin'], name: 'admin.broadcasting.test')]
    public function test(Request $request)
    {
        $user = Auth::user();
        if (!$user instanceof User) {
            return $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $message = trim((string)($request->get('message') ?? 'test broadcast'));

        try {
            broadcast(new MessageBroadcast((int)$user->id, $message))->send();

            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::warning('Broadcasting monitor: test send failed', ['error' => $e->getMessage()]);

            return $this->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * The monitor API assumes the Soketi (Pusher protocol) HTTP client. Any other
     * driver throws, and the caller's catch turns that into ok:false.
     */
    private function pusher(): Pusher
    {
        $driver = Broadcast::driver('pusher');

        if (!$driver instanceof PusherBroadcaster) {
            throw new \RuntimeException('broadcasting: the pusher driver is not configured');
        }

        return $driver->getPusher();
    }

    private function channelType(string $name): string
    {
        if (str_starts_with($name, 'presence-')) {
            return 'presence';
        }
        if (str_starts_with($name, 'private-')) {
            return 'private';
        }

        return 'public';
    }

    /**
     * Connection metadata for the frontend (public key only).
     *
     * @return array<string, mixed>
     */
    private function connectionMeta(): array
    {
        $config = app('config')->get('broadcasting.connections.pusher', []);

        return [
            'driver' => app('config')->get('broadcasting.default'),
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'scheme' => $config['scheme'] ?? null,
        ];
    }
}
