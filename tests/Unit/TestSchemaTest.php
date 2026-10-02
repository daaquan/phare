<?php

test('test schema refuses non-SQLite connections before executing database operations', function (string $driver) {
    $connection = new class($driver)
    {
        public function __construct(private string $driver) {}

        public function getType(): string
        {
            return $this->driver;
        }

        public function execute(string $sql): void
        {
            throw new LogicException('The schema must never execute SQL on this connection.');
        }
    };
    $app = new class($connection)
    {
        public function __construct(private object $connection) {}

        public function make(string $name): object
        {
            return $this->connection;
        }
    };

    expect(fn () => migrateTestSchema($app))->toThrow(
        RuntimeException::class,
        'Refusing to build the test schema on a "' . $driver . '" connection.',
    );
})->with(['mysql', 'pgsql']);
