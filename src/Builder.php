<?php

/**
*  Query Builder
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

use Database;

class Builder
{

    /**
     * Table name
     *
     * @var string
     */
    protected $table;

    /**
     * Select columns
     *
     * @var array
     */
    protected $columns;

    /**
     * Conditions
     *
     * @var array
     */
    protected $where = [];

    /**
     * Condition values stack
     *
     * @var array
     */
    protected $values = [];

    /**
     * Get database
     */
    public static function db()
    {
        return Database::get();
    }

    /**
     * Class constructor
     *
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Add query condition
     *
     * @param string $column
     * @param string $operator
     * @param string|int $value
     * @return object Fudyartanto\C5orm\Builder
     */
    public function where($column, $operator, $value)
    {
        // add value to stack
        $this->values[] = $value;
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => '?'
        ];
        return $this;
    }

    /**
     * Build SQL query
     *
     * @return string
     */
    public function getRawQuery()
    {
        $q = "SELECT * FROM {$this->table}";
        if ($this->where) {
            $where = array_map(function ($v) {
                return "`{$v['column']}` {$v['operator']} {$v['value']}";
            }, $this->where);
            $q .= " WHERE " . implode(" AND ", $where);
        }
        return $q;
    }

    /**
     * Get total records
     *
     * @return int
     */
    public function count()
    {
        $q = "SELECT count(*) FROM (" . $this->getRawQuery() . ") AS `tb`";
        return self::db()->GetOne($q, $this->values);
    }
}