<?php

namespace trinity\contracts;

interface DatabaseConnectionInterface
{
    public function select(array $columns): self;

    public function from(string $table): self;

    public function where(array $conditions): self|false;

    public function andWhere(array $conditions): self;

    public function one(): array;
}