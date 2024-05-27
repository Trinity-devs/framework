<?php

namespace trinity\contracts\database;

interface DatabaseConnectionInterface
{
    public function exec(string $query, array $bindings = []): int;
    public function execute(string $query, array $bindings = []): false|array;

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int;

    public function fetch(string $query, array $bindings = []): array;

    public function fetchCount(string $query, array $values = [], array $bindings = []): int;
}
