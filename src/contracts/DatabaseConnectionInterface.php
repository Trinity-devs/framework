<?php

namespace trinity\contracts;

interface DatabaseConnectionInterface
{
    public function exec(string $query, array $bindings = []): int;

    public function execute(string $query, array $bindings = []): false|array;

    public function select(string $tableName, array $columns, string $condition = null, array $bindings = []): array|false;

    public function selectOne(string $tableName, array $columns, string $condition = null, array $bindings = []): array|false;

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int;

    public function update(string $tableName, array $values, string $condition = null, array $bindings = []): int;

    public function delete(string $tableName, string $condition, array $bindings = []): int;
}