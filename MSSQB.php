<?php

namespace PDOHelper;

/**
 * MySQL Simple Query Builder (MSSQB)
 *
 * @author Sander Tuinstra <sandert2001@hotmail.com>
 * @link https://github.com/SanderT2001/PDOHelper.git
 */
class MSSQB
{
    /**
     * @var array
     */
    private const ORDER_METHODS = ['ASC', 'DESC'];

    /**
     * @var array
     */
    protected $queryStructures = [
        // Base Query: SELECT * FROM table_name WHERE condition;
        'select' => 'SELECT {columns} FROM {table}',
        // Base Query: INSERT INTO table_name (column_list) VALUES (value_list_1), (value_list_2), (value_list_3);
        'insert' => 'INSERT INTO {table} ({columns}) VALUES {values}',
        // Base Query: UPDATE table_name SET column1 = value1, column2 = value2  WHERE condition;
        'update' => 'UPDATE {table} SET {columnsValues}',
        // Base Query: DELETE FROM table_name WHERE condition;
        'delete' => 'DELETE FROM {table}'
    ];

    protected $queryAttributesStructures = [
        // Example: WHERE id = 0;
        'where' => 'WHERE {where}',
        // Example: ORDER BY id DESC;
        'order' => 'ORDER BY {order}',
        // Example: LIMIT 25;
        'limit' => 'LIMIT {limit}'
    ];

    /**
     * @var string
     */
    private $table = null;

    /**
     * @var string
     */
    public $query = '';

    public function __construct() { }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        // Reset Query in case of a new Table name.
        $this->query = '';
        return $this;
    }

    public function getQueryStructure(string $name): string
    {
        return ($this->queryStructures[$name] ?? '');
    }

    public function getQueryAttributeStructure(string $name): string
    {
        return ($this->queryAttributesStructures[$name] ?? '');
    }

    public function addQueryAttribute(string $attribute): self
    {
        $this->query .= (' ' . $this->getQueryAttributeStructure($attribute));
        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function select(): self
    {
        $this->startNewQuery('select');
        // By default select all columns
        $this->columns('*');
        return $this;
    }

    public function insert(array $data): self
    {
        $this->startNewQuery('insert');

        // Validate data
        $baseline = $data[array_key_first($data)];
        $baselineKeys = array_keys($baseline);
        $baselineCount = count($baselineKeys);
        foreach ($data as $entry) {
            if (count(array_keys($entry)) !== $baselineCount) {
                return $this->error('Not all data contains the same amount of keys');
            }

            foreach ($entry as $column => $value) {
                if (!in_array($column, $baselineKeys)) {
                    return $this->error('Not all the data contains the same keys');
                }
            }
        }

        $this->columns($baselineKeys);

        // Prepare the data
        foreach ($data as $key => $entry) {
            foreach ($entry as $column => $value) {
                // Prepare the value
                $sqlReadyValue = $this->escapeQuotes($value);
                $data[$key][$column] = ('"' . $sqlReadyValue . '"');
            }
        }

        // Parse the data
        $sqlValues = [];
        foreach ($data as $entry) {
            $sqlValues[] = '(' . implode(', ', $entry) . ')';
        }
        $sqlValuesStr = implode(', ', $sqlValues);

        $this->parseQueryValues('values', $sqlValuesStr);

        return $this;
    }

    public function update(array $data): self
    {
        $this->startNewQuery('update');

        $columnsValues = $this->buildConditionString($data, ', ');

        $this->parseQueryValues('columnsValues', $columnsValues);
        return $this;
    }

    public function delete(): self
    {
        $this->startNewQuery('delete');
        return $this;
    }

    public function columns($columns): self
    {
        if (empty($this->query)) {
            return $this->error('No Query Selected');
        }

        $values = (is_string($columns)) ? $columns : '*';
        if (is_array($columns)) {
            $values = implode(', ', $columns);
        }

        $this->parseQueryValues('columns', $values);
        return $this;
    }

    public function where(array $conditions): self
    {
        if (empty($this->query)) {
            return $this->error('No Query Selected');
        }

        $this->addQueryAttribute('where')
             ->parseQueryValues('where', $this->buildConditionString($conditions));

        return $this;
    }

    public function order(array $columnsMethods): self
    {
        if (empty($this->query)) {
            return $this->error('No Query Selected');
        }

        $values = '';
        $counter = 0;
        foreach ($columnsMethods as $column => $method) {
            $method = strtoupper($method);
            if (!in_array($method, $this::ORDER_METHODS)) {
                return $this->error('Invalid Order Method, "' . $method . '", expected ' . implode(' OR ', $this::ORDER_METHODS));
            }

            // Prefix the value with a comma when there are multiple order fields?
            $prefix = ($counter === 0) ? '' : ', ';

            $values .= ($prefix . $column . ' ' . $method);
            $counter++;
        }

        $this->addQueryAttribute('order')
             ->parseQueryValues('order', $values);
        return $this;
    }

    public function limit(int $max): self
    {
        if (empty($this->query)) {
            return $this->error('No Query Selected');
        }

        $this->addQueryAttribute('limit')
             ->parseQueryValues('limit', $max);
        return $this;
    }

    private function startNewQuery(string $structureName): self
    {
        $this->query = $this->getQueryStructure($structureName);
        $this->parseQueryValues('table', $this->getTable());
        return $this;
    }

    private function parseQueryValues(string $placeholder, string $values): self
    {
        $placeholder = ('{' . $placeholder . '}');

        $this->query = strtr($this->query, [
            $placeholder => $values
        ]);
        return $this;
    }

    private function buildConditionString(array $conditions, string $glue = ' AND '): string
    {
        $output = '';
        $counter = 0;
        foreach ($conditions as $column => $value) {
            $prefix = ($counter === 0) ? '' : $glue;

            // Prepare the value
            $value = $this->escapeQuotes($value);

            if (stripos($column, 'LIKE') === false) {
                // Normal Statement
                $output .= ($prefix . $column . ' = "' . $value . '"');
            } else {
                // Like Statement
                $output .= ($prefix . $column . ' "%' . $value . '%"');
            }
            $counter++;
        }
        return $output;
    }

    // no return types and param type hint because of when param given is null
    private function escapeQuotes($raw)
    {
        if (!is_string($raw)) {
            return $raw;
        }

        $escaped = $raw;

        // Escape single and double quotes.
        $escaped = str_replace("'", "\'", $escaped);
        $escaped = str_replace('"', '\"', $escaped);
        return $escaped;
    }

    private function error(string $msg)
    {
        throw new \Exception('SqlQueryBuilder: ' . $msg);
    }
}
