<?php

declare(strict_types=1);

namespace trinity\db;

use trinity\contracts\database\DatabaseConnectionInterface;
use trinity\contracts\database\QueryBuilderInterface;

class QueryBuilder implements QueryBuilderInterface
{
    private string $select = '*';
    private string $table = '';
    private string $where = '';
    private array $bindings = [];
    private array $values = [];
    private array $join = [];
    private string $orderBy = '';
    private string $limit = '';
    private string $like = '';

    public function __construct(
        private readonly DatabaseConnectionInterface $connection
    ) {
    }

    public function select(array|string $columns = '*'): self
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;

        return $this;
    }

    public function from(string $table): self
    {
        $this->table = trim($table);

        return $this;
    }

    public function where(array $conditions): self
    {
        $this->where = $this->prepareBindings($conditions);

        return $this;
    }

    public function andWhere(array $conditions): self
    {
        return $this->appendWhere($conditions, 'AND');
    }

    public function orWhere(array $conditions): self
    {
        return $this->appendWhere($conditions, 'OR');
    }

    private function appendWhere(array $conditions, string $operator): self
    {
        $whereClause = $this->prepareBindings($conditions);
        if ($this->where !== '') {
            $this->where .= " $operator $whereClause";

            return $this;
        }

        $this->where = $whereClause;

        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->join[] = "JOIN $table ON $first $operator $second";

        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->join[] = "LEFT JOIN $table ON $first $operator $second";

        return $this;
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->join[] = "RIGHT JOIN $table ON $first $operator $second";

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = "LIMIT $limit";

        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "ORDER BY $column $direction";

        return $this;
    }

    public function like(string $column, string $pattern): self
    {
        $this->like = "$column LIKE ?";
        $this->bindings[] = $pattern;

        return $this;
    }

    private function prepareBindings(array $conditions): string
    {
        $bindings = [];
        foreach ($conditions as $column => $value) {
            $bindings[] = "$column = ?";
            $this->bindings[] = $value;
        }

        return implode(' AND ', $bindings);
    }

    private function get(): array
    {
        $query = "SELECT $this->select FROM $this->table ";

        if (empty($this->join) === false) {
            $query .= implode(' ', $this->join) . ' ';
        }

        if ($this->where !== '') {
            $query .= "WHERE $this->where ";
        }

        if ($this->like !== '') {
            $query .= ($this->where !== '' ? 'AND ' : 'WHERE ') . $this->like . ' ';
        }

        if ($this->orderBy !== '') {
            $query .= $this->orderBy . ' ';
        }

        if ($this->limit !== '') {
            $query .= $this->limit . ' ';
        }

        return $this->connection->execute($query, $this->bindings);
    }

    public function one(): array
    {
        $this->limit(1);
        $results = $this->get();

        return $results[0] ?? [];
    }

    public function all(): array
    {
        return $this->get();
    }

    public function scalar(): mixed
    {
        $this->limit(1);
        $results = $this->get();
        $firstRow = $results[0] ?? [];

        return reset($firstRow) ?: null;
    }

    public function delete(): int
    {
        $query = "DELETE FROM $this->table ";
        if ($this->where !== '') {
            $query .= "WHERE $this->where ";
        }
        
        return $this->connection->exec($query, $this->bindings);
    }

    public function update(array $values): int
    {
        $setClause = implode(', ', array_map(fn($col) => "$col = ?", array_keys($values)));
        $query = "UPDATE $this->table SET $setClause ";
        if ($this->where !== '') {
            $query .= "WHERE $this->where ";
        }

        return $this->connection->exec($query, array_merge(array_values($values), $this->bindings));
    }

    public function insert(string $tableName, array $values, string $condition = null, array $bindings = []): int
    {
        $columns = implode(", ", array_keys($values));
        $placeholders = implode(", ", array_fill(0, count($values), "?"));
        $query = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";

        if ($condition !== null) {
            $query .= " $condition";
        }

        return $this->connection->exec($query, array_merge(array_values($values), $bindings));
    }

    public function batchInsert(string $tableName, array $values, array $bindings = []): int
    {
        $columns = implode(", ", array_keys($values[0]));
        $placeholders = implode(", ", array_fill(0, count($values[0]), "?"));
        $allPlaceholders = implode(", ", array_fill(0, count($values), "($placeholders)"));
        $query = "INSERT INTO $tableName ($columns) VALUES $allPlaceholders";

        $flatValues = [];
        foreach ($values as $valueSet) {
            foreach ($valueSet as $value) {
                $flatValues[] = $value;
            }
        }

        return $this->connection->exec($query, array_merge($flatValues, $bindings));
    }

    public function getRawSql(): string
    {
        $query = "SELECT $this->select FROM $this->table ";
        if (empty($this->join) === false) {
            $query .= implode(' ', $this->join) . ' ';
        }
        if ($this->where !== '') {
            $query .= "WHERE $this->where ";
        }
        if ($this->like !== '') {
            $query .= ($this->where !== '' ? 'AND ' : 'WHERE ') . $this->like . ' ';
        }
        if ($this->orderBy !== '') {
            $query .= $this->orderBy . ' ';
        }
        if ($this->limit !== '') {
            $query .= $this->limit . ' ';
        }

        $indexedBindings = array_values($this->bindings);

        foreach ($indexedBindings as $index => $value) {
            $query = preg_replace('/\?/', $this->quote($value), $query, 1);
        }

        return $query;
    }

    private function quote($value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string)$value;
        }
        return "'" . str_replace("'", "''", $value) . "'";
    }

    public function count(): int
    {
        $query = "SELECT COUNT(*) as count FROM $this->table ";
        if (empty($this->join) === false) {
            $query .= implode(' ', $this->join) . ' ';
        }

        if ($this->where !== '') {
            $query .= "WHERE $this->where ";
        }

        if ($this->like !== '') {
            $query .= ($this->where !== '' ? 'AND ' : 'WHERE ') . $this->like . ' ';
        }

        $result = $this->connection->execute($query, $this->bindings);

        return $result[0]['count'] ?? 0;
    }

    public function exec(string $query, array $bindings = []): int
    {
        return $this->connection->exec($query, $bindings);
    }

    public function execute(string $query, array $bindings = []): false|array
    {
        return $this->connection->execute($query, $bindings);
    }

    public function fetch(string $query, array $bindings = []): array
    {
      return $this->connection->fetch($query, $bindings);
    }

    public function fetchCount(string $query, array $values = [], array $bindings = []): int
    {
        return $this->connection->fetchCount($query, $values, $bindings);
    }
}
