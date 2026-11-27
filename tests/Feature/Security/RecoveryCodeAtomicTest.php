<?php

use App\Models\User;
use App\Support\Concurrency\FileLock;

/*
 * Recovery-code consumption is a locked read-modify-write on freshly reloaded
 * state. The "database" here is a file so forked workers share it without the
 * SQLite ORM write path.
 */

function recoveryUser(string $store, bool $failSave = false): User
{
    return new class($store, $failSave) extends User
    {
        public function __construct(private string $store, private bool $failSave)
        {
            $this->id = 5;
        }

        protected function reloadRecoveryCodes(): void
        {
            $this->two_factor_recovery_codes = (string)file_get_contents($this->store);
            usleep(20000); // widen the race window between reload and save
        }

        public function save(?array $attributes = null): bool
        {
            if ($this->failSave) {
                return false;
            }
            file_put_contents($this->store, (string)$this->two_factor_recovery_codes);

            return true;
        }
    };
}

beforeEach(function () {
    $this->lockDir = FileLock::$directory;
    $this->tmp = tempnam(sys_get_temp_dir(), 'phare-rc');
    $this->logFile = tempnam(sys_get_temp_dir(), 'phare-log');
    $this->oldLog = ini_get('error_log');
    file_put_contents($this->tmp, '["AAAAAAAAAA","BBBBBBBBBB"]');
});

afterEach(function () {
    FileLock::$directory = $this->lockDir;
    ini_set('error_log', (string)$this->oldLog);
    @unlink($this->tmp);
    @unlink($this->logFile);
});

test('concurrent requests cannot spend the same recovery code twice', function () {
    $workers = 4;
    $pids = [];
    for ($i = 0; $i < $workers; $i++) {
        $pid = pcntl_fork();
        if ($pid === 0) {
            exit(recoveryUser($this->tmp)->consumeRecoveryCode('AAAAAAAAAA') ? 0 : 1);
        }
        $pids[] = $pid;
    }

    $wins = 0;
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
        $wins += pcntl_wexitstatus($status) === 0 ? 1 : 0;
    }

    expect($wins)->toBe(1)
        ->and(json_decode((string)file_get_contents($this->tmp), true))->toHaveCount(1)->and((string)file_get_contents($this->tmp))->not->toContain('AAAAAAAAAA', 'BBBBBBBBBB');
})->skip(!function_exists('pcntl_fork'), 'pcntl not available');

test('a stale in-memory copy cannot respend a code already consumed elsewhere', function () {
    $stale = recoveryUser($this->tmp);
    $stale->two_factor_recovery_codes = '["AAAAAAAAAA","BBBBBBBBBB"]';
    expect(recoveryUser($this->tmp)->consumeRecoveryCode('AAAAAAAAAA'))->toBeTrue()
        ->and($stale->consumeRecoveryCode('AAAAAAAAAA'))->toBeFalse()
        ->and($stale->consumeRecoveryCode('BBBBBBBBBB'))->toBeTrue();
});

test('a recovery code is refused when the lock is unavailable', function () {
    ini_set('error_log', $this->logFile);
    FileLock::$directory = $this->tmp . '/nope'; // regular file as parent: mkdir/fopen fail

    expect(recoveryUser($this->tmp)->consumeRecoveryCode('AAAAAAAAAA'))->toBeFalse()
        ->and(file_get_contents($this->tmp))->toBe('["AAAAAAAAAA","BBBBBBBBBB"]')
        ->and(file_get_contents($this->logFile))->toContain('refusing code');
});

test('a recovery code is refused and left in place when the save fails', function () {
    ini_set('error_log', $this->logFile);
    $user = recoveryUser($this->tmp, failSave: true);

    expect($user->consumeRecoveryCode('AAAAAAAAAA'))->toBeFalse()
        ->and(file_get_contents($this->tmp))->toBe('["AAAAAAAAAA","BBBBBBBBBB"]');
});
