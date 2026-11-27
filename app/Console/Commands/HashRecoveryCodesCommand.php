<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Concurrency\FileLock;
use Phare\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * One-off, idempotent upgrade of legacy plaintext two-factor recovery codes to
 * keyed hashes. Codes are also upgraded lazily the next time a user spends one,
 * so running this is only needed to remove plaintext from rest immediately.
 */
#[AsCommand(name: 'two-factor:hash-recovery-codes', description: 'Hash legacy plaintext two-factor recovery codes.')]
class HashRecoveryCodesCommand extends Command
{
    public function handle()
    {
        $upgraded = 0;
        $failed = 0;

        foreach (User::find() as $row) {
            try {
                // Same lock + fresh read as consumeRecoveryCode(), so a concurrent
                // login cannot have its spent code written back.
                $changed = FileLock::run('recovery_codes_' . (int)$row->id, function () use ($row): bool {
                    $user = User::findFirst((int)$row->id);
                    if (!$user instanceof User || !$user->upgradeLegacyRecoveryCodes()) {
                        return false;
                    }
                    if ($user->save() === false) {
                        throw new \RuntimeException('save failed');
                    }

                    return true;
                });
                $upgraded += $changed ? 1 : 0;
            } catch (\Throwable $e) {
                $failed++;
                $this->output->writeError('User ' . (int)$row->id . ': ' . $e->getMessage());
            }
        }

        $this->output->writeInfo("Hashed recovery codes for {$upgraded} user(s); {$failed} failed.");

        return $failed === 0 ? 0 : 1;
    }
}
