<?php

namespace App\Console\Commands;

use App\Models\User;
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
            // Same lock + fresh read + compare-and-swap as every other writer.
            $user = User::findFirst((int)$row->id);
            if (!$user instanceof User) {
                continue;
            }
            if ($user->hashLegacyRecoveryCodes()) {
                $upgraded++;
            } elseif ($user->upgradeLegacyRecoveryCodes()) {
                // legacy entries present but the locked write was refused
                $failed++;
                $this->output->writeError('User ' . (int)$user->id . ': upgrade refused (see log for stage).');
            }
        }

        $this->output->writeInfo("Hashed recovery codes for {$upgraded} user(s); {$failed} failed.");

        return $failed === 0 ? 0 : 1;
    }
}
