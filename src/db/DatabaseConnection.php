<?php

namespace src\db;

use src\contracts\DatabaseConnectionInterface;
use PDO;

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

    /**
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function exec(string $query, array $bindings = []): int
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return false|array
     */
    public function execute(string $query, array $bindings = []): false|array
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $tableName
     * @param array $columns
     * @param string|null $condition
     * @param array $bindings
     * @return array|false
     */
    public function select(string $tableName, array $columns, string $condition = null, array $bindings = []): array|false
    {
        if (empty($columns)) {
            return false;
        }

        $query = "SELECT " . implode(", ", $columns) . " FROM " . $tableName;
        if ($condition !== null) {
            $query .= " WHERE " . $condition;
        }

        return $this->execute($query, $bindings);
    }

    /**
     * @param string $tableName
     * @param array $columns
     * @param string|null $condition
     * @param array $bindings
     * @return array|false
     */
    public function selectOne(string $tableName, array $columns, string $condition = null, array $bindings = []): array|false
    {
        if (empty($columns)) {
            return false;
        }

        $result = $this->select($tableName, $columns, $condition, $bindings);

        return empty($result) === true ? false : $result[0];
    }

    /**
     * @param string $tableName
     * @param array $values
     * @param string|null $condition
     * @param array $bindings
     * @return int
     */
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

    /**
     * @param string $tableName
     * @param array $values
     * @param string|null $condition
     * @param array $bindings
     * @return int
     */
    public function update(string $tableName, array $values, string $condition = null, array $bindings = []): int
    {
        $setValues = [];
        foreach ($values as $column => $value) {
            $setValues[] = "$column = ?";
            $bindings[] = $value;
        }
        $query = "UPDATE " . $tableName . " SET " . implode(", ", $setValues);
        if ($condition !== null) {
            $query .= " WHERE " . $condition;
        }
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    /**
     * @param string $tableName
     * @param string $condition
     * @param array $bindings
     * @return int
     */
    public function delete(string $tableName, string $condition, array $bindings = []): int
    {
        $query = "DELETE FROM " . $tableName . " WHERE " . $condition;
        $statement = $this->pdo->prepare($query);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}