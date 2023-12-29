<?php

namespace trinity\db;

use PDO;
use trinity\contracts\database\DatabaseConnectionInterface;
use trinity\exception\databaseException\PDOException;

class DatabaseConnection implements DatabaseConnectionInterface
{
    private PDO|null $pdo;
    private string $select = '';
    private string $table = '';
    private string $where = '';
    private array $bindings = [];

    /**
     * @param array $pdoConfiguration
     */
    public function __construct(array $pdoConfiguration)
    {
        $this->pdo = new PDO(...$pdoConfiguration);
    }


    /**
     * @param array $columns
     * @return $this
     */
    public function select(array $columns = ['*']): self
    {
        $this->select = implode(', ', $columns);

        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function from(string $table): self
    {
        $this->table = trim($table);

        return $this;
    }

    /**
     * @param array $conditions
     * @return DatabaseConnection|false
     */
    public function where(array $conditions): self|false
    {
        $this->bindings = $conditions;

        $whereClause = '';
        if (count($conditions) === 1) {
            $column = key($conditions);
            $whereClause = "$column = :$column";
        }

        if (count($conditions) > 1) {
            $preparedConditions = [];
            foreach ($conditions as $column => $value) {
                $preparedConditions[] = "$column = :$column";
            }

            $whereClause = implode(' AND ', $preparedConditions);
        }

        $this->where = "$whereClause";

        return $this;
    }

    public function andWhere(array $conditions): self
    {
        $this->bindings = array_merge($this->bindings, $conditions);

        $whereClause = '';
        if (count($conditions) === 1) {
            $column = key($conditions);
            $whereClause = "$column = :$column";
        }

        if (count($conditions) > 1) {
            $preparedConditions = [];
            foreach ($conditions as $column => $value) {
                $preparedConditions[] = "$column = :$column";
            }

            $whereClause = implode(' AND ', $preparedConditions);
        }
        if ($this->where !== '') {
            $this->where .= " AND $whereClause";
        }

        if ($this->where === '') {
            $this->where = "$whereClause";
        }

        return $this;
    }

    public function one(): array
    {
        $query = "SELECT $this->select FROM $this->table WHERE $this->where";

        if ($this->where === '') {
            $query = "SELECT $this->select FROM $this->table";
        }

        $statement = $this->pdo->prepare($query);

        $statement->execute($this->bindings);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result !== []) {
            return array_shift($result);
        }

        return $result;
    }

    public function all(): array
    {
        $query = "SELECT $this->select FROM $this->table WHERE $this->where";

        if ($this->where === '') {
            $query = "SELECT $this->select FROM $this->table";
        }

        $statement = $this->pdo->prepare($query);

        $statement->execute($this->bindings);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result;
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