<?php

namespace trinity\contracts\database;

interface QueryBuilderInterface extends DatabaseConnectionInterface
{
    public function select(array|string $columns): self;

    public function from(string $table): self;

    public function where(array $conditions): self;

    public function andWhere(array $conditions): self;

    public function orWhere(array $conditions): self;

    public function join(string $table, string $first, string $operator, string $second): self;

    public function leftJoin(string $table, string $first, string $operator, string $second): self;

    public function rightJoin(string $table, string $first, string $operator, string $second): self;

    public function like(string $column, string $pattern): self;

    public function orderBy(string $column, string $direction): self;

    public function limit(int $limit): self;

    public function one(): array;

    public function all(): array;

    public function scalar(): mixed;

    public function delete(): int;

    public function update(array $values): int;

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int;

    public function batchInsert(string $tableName, array $values, array $bindings = []): int;

    public function getRawSql(): string;
    
    public function count(): int;
}
