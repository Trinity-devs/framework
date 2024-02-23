<?php

namespace trinity\db;

use Throwable;
use trinity\contracts\database\DatabaseConnectionInterface;
use trinity\contracts\database\QueryBuilderInterface;
use trinity\exception\databaseException\PDOException;

class QueryBuilder implements QueryBuilderInterface
{
    private string $select = '';
    private string $table = '';
    private string $where = '';
    private array $bindings = [];
    private array $values = [];
    private array $join = [];

    public function __construct(
        private readonly DatabaseConnectionInterface $connection
    ) {
    }

    /**
     * @param array|string $columns
     * @return $this
     */
    public function select(array|string $columns = '*'): self
    {
        if (is_array($columns) === true) {
            $this->select = implode(', ', $columns);
        }

        if (is_array($columns) === false) {
            $this->select = $columns;
        }

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
     * @return $this
     */
    public function where(array $conditions): self
    {
        $whereClause = $this->prepareBindings($conditions);

        $this->where = $whereClause;

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     */
    public function andWhere(array $conditions): self
    {
        $whereClause = $this->prepareBindings(array_merge($this->bindings, $conditions));

        if ($this->where !== '') {
            $this->where .= " AND $whereClause";
        }

        if ($this->where === '') {
            $this->where = $whereClause;
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     */
    public function orWhere(array $conditions): self
    {
        $whereClause = $this->prepareBindings(array_merge($this->bindings, $conditions));

        if ($this->where !== '') {
            $this->where .= " OR $whereClause";
        }

        if ($this->where === '') {
            $this->where = $whereClause;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function one(): array
    {
        try {
            $query = $this->prepareQuery();

            $result = $this->fetch($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }

        if ($result !== []) {
            return array_shift($result);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function all(): array
    {
        try {
            $query = $this->prepareQuery();

            return $this->fetch($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function exec(string $query, array $bindings = []): int
    {
        try {
            return $this->connection->exec($query, $bindings);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @param array $bindings
     * @return false|array
     */
    public function execute(string $query, array $bindings = []): false|array
    {
        try {
            return $this->connection->execute($query, $bindings);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
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
        try {
            return $this->connection->insert($tableName, $values, $condition, $bindings);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    public function batchInsert(string $tableName, array $values, array $bindings = []): int
    {
        $this->table = $tableName;
        $this->values = $values;
        $this->where = $this->prepareBindings($bindings);

        $setValues = [];
        $columnImplode = [];

        foreach ($values as $column => $value) {
            $columnImplode[] = $column;
            $setValues[] = ":$column";
        }

        $columns = implode(", ", $columnImplode);
        $placeholders = implode(", ", $setValues);

        $query = "INSERT INTO $this->table ($columns) VALUES ($placeholders)";

        if ($this->where !== '') {
            $query .= " WHERE $this->where";
        }

        try {
            return $this->fetchCount($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param string $tableName
     * @param array $values
     * @param array $bindings
     * @return int
     */
    public function update(string $tableName, array $values, array $bindings = []): int
    {
        $this->table = $tableName;
        $whereClause = $this->prepareBindings($bindings);
        $this->values = $values;

        $this->where = $whereClause;

        $setValues = [];
        foreach ($values as $column => $value) {
            $setValues[] .= "$column=:$column";
        }

        $query = "UPDATE $this->table SET " . implode(", ", $setValues);

        if ($this->bindings !== null) {
            $query .= " WHERE $this->where";
        }

        try {
            return $this->fetchCount($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param string $tableName
     * @param array $bindings
     * @return int
     */
    public function delete(string $tableName, array $bindings = []): int
    {
        $this->table = $tableName;

        $this->where = $this->prepareBindings($bindings);

        $query = "DELETE FROM $this->table WHERE $this->where";

        try {
            return $this->fetchCount($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @param string $query
     * @return array
     */
    private function fetch(string $query): array
    {
        try {
            return $this->connection->fetch($query, $this->bindings);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @return string
     */
    private function prepareQuery(): string
    {
        $query = "SELECT $this->select FROM $this->table";

        if ($this->join !== []) {
            foreach ($this->join as $join) {
                $on = $join['on'];
                $query .= " $join[type] $join[table] ON $on";
            }
        }

        if ($this->where !== '') {
            $query .= " WHERE $this->where";
        }

        return $query;
    }

    /**
     * @param array $inputArray
     * @return string
     */
    private function prepareBindings(array $inputArray = []): string
    {
        $this->bindings = $inputArray;

        $whereClause = '';
        $count = count($inputArray);

        if ($count === 1) {
            $column = key($inputArray);
            $whereClause = "$column=:$column";
        }
        if ($count > 1) {
            $preparedConditions = [];
            foreach ($inputArray as $column => $value) {
                $preparedConditions[] = "$column=:$column";
            }

            $whereClause = implode(' AND ', $preparedConditions);
        }

        return $whereClause;
    }

    /**
     * Возвращает $statement->rowCount()
     *
     * @param string $query
     * @return int
     */
    private function fetchCount(string $query): int
    {
        try {
            return $this->connection->fetchCount($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function scalar(): mixed
    {
        try {

        $query = $this->prepareQuery();

        $result = $this->fetch($query);
        } catch (Throwable $e) {
            throw new PDOException($e->getMessage());
        }

        if (empty($result) === true) {
            return '';
        }

        return reset($result[0]);
    }


    /**
     * @param string $table
     * @param array|string $on
     * @return $this
     */
    public function join(string $table, array|string $on = ''): self
    {
        $this->resultJoin('JOIN', $table, $on);

        return $this;
    }

    /**
     * @param string $table
     * @param array|string $on
     * @return $this
     */
    public function leftJoin(string $table, array|string $on = ''): self
    {
        $this->resultJoin('LEFT JOIN', $table, $on);

        return $this;
    }

    /**
     * @param string $table
     * @param array|string $on
     * @return $this
     */
    public function rightJoin(string $table, array|string $on = ''): self
    {
        $this->resultJoin('RIGHT JOIN', $table, $on);

        return $this;
    }

    /**
     * @param string $type
     * @param string $table
     * @param array|string $on
     * @return void
     */
    private function resultJoin(string $type, string $table, array|string $on = ''): void
    {
        $this->join[] = ['type' => $type, 'table' => $table, 'on' => $on];
    }

    /**
     * @return $this
     */
    public function beginTransaction(): self
    {
        $this->fetch('START TRANSACTION;');

        return $this;
    }

    /**
     * @return void
     */
    public function commit(): void
    {
        $this->fetch('COMMIT;');
    }

    /**
     * @return void
     */
    public function rollback(): void
    {
        $this->fetch('ROLLBACK;');
    }
}