<?php

namespace App\Console\Commands\Migrate;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phox\Database\MySql\DatabaseManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'migrate', description: 'Run the database migrations')]
class MigrateCommand extends \Phox\Console\Command
{
    private array $migrations = [];

    public function handle()
    {
        /** @var DatabaseManager $dbManager */
        $dbManager = app()['dbManager'];

        foreach (glob(\App::databasePath('migrations/*'), GLOB_ONLYDIR) as $directory) {
            $database = basename($directory);
            $config = config("database.connections.{$database}");

            if ($config->path('shards')) {
                foreach ($config->path('shards') as $shardId => $shard) {
                    $shardConfig = $shard->toArray();
                    $shardConfig['driver'] = $config['driver'];
                    $shardConfig['host'] = $shard['write'];
                    $conn = $dbManager->getConnection($shardConfig, $shardId, 'write');
                    $this->migrateDatabase($conn, "migrations/$database");
                }
            } else {
                $conn = $dbManager->getConnection($config->toArray());
                $this->migrateDatabase($conn, "migrations/$database");
            }
        }

        if ($this->migrations) {
            $this->output->writeInfo("\n<fg=white;bg=blue> INFO </> Migration table created successfully.\n");
        } else {
            $this->output->writeInfo("\n<fg=white;bg=blue> INFO </> Nothing to migrate.\n");
        }
    }

    private function migrateDatabase(Mysql $conn, $path)
    {
        $this->createMigrationsTable($conn);
        $start = microtime(true);

        foreach (glob(\App::databasePath("$path/*.sql")) as $file) {
            $filename = basename($file, '.sql');

            $exists = $conn->query("SELECT * FROM migrations WHERE migration = '{$filename}'");
            if ($exists->fetch()) {
                continue;
            }

            try {
                $sqlContent = file_get_contents($file);
                if ($conn->execute($sqlContent)) {
                    $conn->execute("INSERT INTO migrations (migration, batch) VALUES ('{$filename}', 1)");
                }

                $time = number_format((microtime(true) - $start), 3);
                $this->output->writeInfo("Migrated: {$filename} ({$time}ms)");
            } catch (\Exception $e) {
                $this->output->writeError("Migration failed for {$filename}: " . $e->getMessage());
            }

            $start = microtime(true);

            $this->migrations[] = $filename;
        }
    }

    private function createMigrationsTable(Mysql $conn)
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;';

        return $conn->execute($sql);
    }
}
