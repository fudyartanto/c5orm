<?php

/**
*  Query Builder
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

use Database;

defined('C5_EXECUTE') or die('Access Denied.');

class Builder
{

    /**
     * Table name
     *
     * @var string
     */
    protected $table;

    /**
     * Model class name
     *
     * @var object
     */
    protected $modelClass;

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
    public function __construct($table, $modelClass)
    {
        $this->table = $table;
        $this->modelClass = $modelClass;
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
        return self::db()->GetOne("SELECT count(*) FROM (" . $this->getRawQuery() . ") AS `tb`", $this->values);
    }

    /**
     * Get first occurrence record
     *
     * @return object
     */
    public function first()
    {
        if ($result = self::db()->GetRow($this->getRawQuery(), $this->values)) {
            $model = new $this->modelClass;
            foreach ($result as $key => $val) {
                $model->{$key} = $val;
            }
            return $model;
        }
    }

    /**
     * Get data
     *
     * @return cobject Collections
     */
    public function get()
    {
        if ($result = self::db()->GetAll($this->getRawQuery(), $this->values)) {
            return new Collections(
                array_map(function($v) {
                    $modelClass = new $this->modelClass;
                    foreach ($v as $prop => $value) {
                        $modelClass->{$prop} = $value;
                    }
                    return $modelClass;
                }, $result)
            );
        } else {
            return new Collections([]);
        }
    }
}