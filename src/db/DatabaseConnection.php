<?php

namespace trinity\db;

use PDO;
use trinity\contracts\database\DatabaseConnectionInterface;

class DatabaseConnection implements DatabaseConnectionInterface
{
    private PDO|null $pdo;

    /**
     * @param array $pdoConfiguration
     */
    public function __construct(array $pdoConfiguration)
    {
        $this->pdo = new PDO(...$pdoConfiguration);
    }

    public function execute(string $query, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int
    {
        $columns = implode(", ", array_keys($values));
        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $query = "INSERT INTO " . $tableName . " (" . $columns . ") VALUES (" . $placeholders . ")";

        if ($condition !== null) {
            $query .= " " . $condition;
        }

        $statement = $this->pdo->prepare($query);
        $statement->execute(array_merge(array_values($values), $bindings));

        return $statement->rowCount();
    }

    public function fetch(string $query, array $bindings = []): array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchCount(string $query, array $values = [], array $bindings = []): int
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute(array_merge($values, $bindings));

        return $statement->rowCount();
    }

    public function exec(string $query, array $bindings = []): int
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->rowCount();
    }
}
