<?php

namespace trinity\contracts\database;

interface DatabaseConnectionInterface
{
    public function select(array|string $columns): self;

    public function from(string $table): self;

    public function where(array $conditions): self;

    public function andWhere(array $conditions): self;

    public function one(): array;

    public function all(): array;

    public function scalar(): mixed;

    public function leftJoin(string $table, array|string $on = ''): self;

    public function rightJoin(string $table, array|string $on = ''): self;

    public function join(string $table, array|string $on = ''): self;

    public function delete(string $tableName, array $bindings = []): int;

    public function update(string $tableName, array $values, array $bindings = []): int;

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int;

    function batchInsert(string $tableName, array $values, array $bindings = []): int;

    public function execute(string $query, array $bindings = []): false|array;

    public function exec(string $query, array $bindings = []): int;

    public function orWhere(array $conditions): self;

    public function beginTransaction(): self;

    public function commit(): void;

    public function rollback(): void;
}