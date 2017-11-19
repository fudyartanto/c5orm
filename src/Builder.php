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
    const WHERE_TYPE_IN = 'IN';

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
     * Join
     *
     * @var array
     */
    protected $join = [];

    /**
     * Limit
     *
     * @var integer
     */
    protected $limit;

    /**
     * Offset
     *
     * @var integer
     */
    protected $offset;

    /**
     * Group By
     *
     * @var array
     */
    protected $groupBy = [];

    /**
     * Order by
     *
     * @var array
     */
    protected $orderBy = [];

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
     * Add select query
     *
     * @param string $column
     * @return Fudyartanto\C5orm\Builder
     */
    public function select($column)
    {
        $this->columns[] = $column;
        return $this;
    }

    /**
     * Add join query
     *
     * @param string $table
     * @param string $colLeft
     * @param string $operator
     * @param string $colRight
     * @return Fudyartanto\C5orm\Builder
     */
    public function join($table, $colLeft, $operator, $colRight)
    {
        $this->join[] = [
            'table' => $table,
            'colLeft' => $colLeft,
            'operator' => $operator,
            'colRight' => $colRight
        ];
        return $this;
    }

    /**
     * Add query condition
     *
     * @param string $column
     * @param string $operator
     * @param string|int $value
     * @return Fudyartanto\C5orm\Builder
     */
    public function where($column, $operator, $value)
    {
        // add value to stack
        $this->values[] = $value;
        // add where condition to stack
        $this->where[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => '?'
        ];
        return $this;
    }

    /**
     * Add where in condition
     *
     * @param string $column
     * @param array $values
     * @return Fudyartanto\C5orm\Builder
     */
    public function whereIn($column, $values) {
        // add value to stack
        $this->values = array_merge($this->values, $values);

        $values = array_map(function($v) {
            return '?';
        }, $values);
        $this->where[] = [
            'column' => $column,
            'value' => implode(',', $values),
            'type' => self::WHERE_TYPE_IN
        ];
        return $this;
    }

    /**
     * Add limit query
     *
     * @param integer $limit
     * @return Fudyartanto\C5orm\Builder
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add offset query
     *
     * @param integer $offset
     * @return Fudyartanto\C5orm\Builder
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add group by query
     *
     * @param integer $column
     * @return Fudyartanto\C5orm\Builder
     */
    public function groupBy($column)
    {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * Add order by query
     *
     * @param string $column
     * @param string $direction ASC|DESC
     * @return Fudyartanto\C5orm\Builder
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = $column . ' ' . $direction;
        return $this;
    }

    /**
     * Get where clause
     *
     * @return string
     */
    public function getWhereClause()
    {
        if ($this->where) {
            $where = array_map(function ($v) {
                if (isset($v['type']) && $v['type'] == self::WHERE_TYPE_IN) {
                    return "{$v['column']} IN ({$v['value']})";
                } else {
                    return "{$v['column']} {$v['operator']} {$v['value']}";
                }
            }, $this->where);
            return " WHERE " . implode(" AND ", $where);
        }
    }

    /**
     * Build SQL query
     *
     * @return string
     */
    public function getRawQuery()
    {
        $select = $this->columns ? implode(',', $this->columns) : '*';
        $q = "SELECT {$select} FROM {$this->table}";
        if ($this->join) {
            $join = array_map(function ($v) {
                return "{$v['table']} ON ({$v['colLeft']} {$v[operator]} {$v['colRight']})";
            }, $this->join);
            $q .= " JOIN " . implode(" JOIN ", $join);
        }

        $q .= $this->getWhereClause();

        if ($this->groupBy) {
            $q .= " GROUP BY " . implode(',', $this->groupBy);
        }
        if ($this->orderBy) {
            $q .= " ORDER BY " . implode(',', $this->orderBy);
        }
        if ($this->limit) {
            $q .= " LIMIT {$this->limit}" ;  
        }
        if ($this->offset) {
            $q .= " OFFSET {$this->offset}" ;  
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

    /**
     * Pluck result by column name
     *
     * @param string $column
     * @return array
     */
    public function pluck($column)
    {
        if ($result = $this->get()->data()) {
            return array_map(function($obj) use ($column) {
                return $obj->{$column};
            }, $result);
            return $result;
        } else {
            return [];
        }
    }

    /**
     * Deleting model by query
     *
     * @return bool
     */
    public  function delete()
    {
        $db = Database::connection();
        return $db->Execute("DELETE FROM {$this->table} {$this->getWhereClause()}", $this->values);
    }
}