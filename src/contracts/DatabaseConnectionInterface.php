<?php

namespace trinity\contracts;

interface DatabaseConnectionInterface
{
    public function select(array $columns): self;

    public function from(string $table): self;

    public function where(array $conditions): self;

    public function andWhere(array $conditions): self;

    public function one(): array;

    public function all(): array;

    public function scalar(): mixed;

    public function leftJoin(string $table, array|string $on = ''): self;

    public function rightJoin(string $table, array|string $on = ''): self;

    public function join(string $table, array|string $on = ''): self;
}