<?php

namespace App\Console\Commands\Migrate;

use Phalcon\Db\Adapter\Pdo\AbstractPdo as PdoConnection;
use Phalcon\Db\Enum;
use Phare\Console\Command;
use Phare\Database\MySql\DatabaseManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'migrate', description: 'Run the database migrations')]
class MigrateCommand extends Command
{
    private array $migrations = [];

    public function handle()
    {
        /** @var DatabaseManager $dbManager */
        $dbManager = app()['dbManager'];

        foreach (glob(\App::databasePath('migrations/*'), GLOB_ONLYDIR) as $directory) {
            $database = basename($directory);
            $config = config("database.connections.{$database}");

            $conn = $dbManager->getConnection($config->toArray());
            $this->migrateDatabase($conn, "migrations/$database");
        }

        if ($this->migrations) {
            $this->output->writeInfo("\n<fg=white;bg=blue> INFO </> Migration table created successfully.\n");
        } else {
            $this->output->writeInfo("\n<fg=white;bg=blue> INFO </> Nothing to migrate.\n");
        }
    }

    private function migrateDatabase(PdoConnection $conn, string $path): void
    {
        $this->createMigrationsTable($conn);
        $driver = $conn->getType();

        foreach (glob(\App::databasePath("$path/*.sql")) as $file) {
            $filename = basename($file, '.sql');

            $row = $conn->fetchOne(
                'SELECT id FROM migrations WHERE migration = ?',
                Enum::FETCH_ASSOC,
                [$filename]
            );

            if ($row) {
                continue;
            }

            $start = microtime(true);

            try {
                $sqlContent = file_get_contents($file);

                if ($this->isPostgres($driver)) {
                    $sqlContent = $this->convertMysqlToPostgres($sqlContent);
                }

                $conn->begin();

                if (!$conn->execute($sqlContent)) {
                    throw new \RuntimeException('execute() returned false');
                }

                $conn->execute(
                    'INSERT INTO migrations (migration, batch) VALUES (?, 1)',
                    [$filename]
                );

                $conn->commit();

                $elapsedMs = number_format((microtime(true) - $start) * 1000, 3);
                $this->output->writeInfo("Migrated: {$filename} ({$elapsedMs}ms)");
                $this->migrations[] = $filename;
            } catch (\Throwable $e) {
                if ($conn->isUnderTransaction()) {
                    $conn->rollback();
                }
                $this->output->writeError("Migration failed for {$filename}: " . $e->getMessage());
            }
        }
    }

    private function isPostgres(string $driver): bool
    {
        return $driver === 'pgsql' || $driver === 'postgresql';
    }

    private function createMigrationsTable(PdoConnection $conn): void
    {
        $driver = $conn->getType();

        if ($this->isPostgres($driver)) {
            $sql = 'CREATE TABLE IF NOT EXISTS migrations (
  id SERIAL PRIMARY KEY,
  migration VARCHAR(191) NOT NULL,
  batch INTEGER NOT NULL
);';
        } else {
            $sql = 'CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;';
        }

        $conn->execute($sql);
    }

    private function convertMysqlToPostgres(string $sql): string
    {
        // Remove backtick quoting
        $sql = str_replace('`', '"', $sql);

        // AUTO_INCREMENT → SERIAL/BIGSERIAL
        $sql = preg_replace('/bigint\s+unsigned\s+NOT\s+NULL\s+AUTO_INCREMENT/i', 'BIGSERIAL NOT NULL', $sql);
        $sql = preg_replace('/int\s+unsigned\s+NOT\s+NULL\s+AUTO_INCREMENT/i', 'SERIAL NOT NULL', $sql);
        $sql = preg_replace('/AUTO_INCREMENT/i', '', $sql);

        // MySQL int types → INTEGER/BIGINT
        $sql = preg_replace('/bigint\s+unsigned/i', 'BIGINT', $sql);
        $sql = preg_replace('/int\s+unsigned/i', 'INTEGER', $sql);

        // datetime → TIMESTAMP
        $sql = preg_replace('/\bdatetime\b/i', 'TIMESTAMP', $sql);

        // Remove ENGINE=..., CHARSET=..., COLLATE=... table options
        $sql = preg_replace('/\)\s*ENGINE\s*=\s*\w+[^;]*/i', ')', $sql);

        // UNIQUE KEY name (col) → UNIQUE (col)
        $sql = preg_replace('/UNIQUE\s+KEY\s+\S+\s*\(([^)]+)\)/i', 'UNIQUE ($1)', $sql);

        // Remove non-unique KEY (index) definitions inside CREATE TABLE
        $sql = preg_replace('/,\s*KEY\s+\S+\s*\([^)]+\)/i', '', $sql);

        return $sql;
    }
}
