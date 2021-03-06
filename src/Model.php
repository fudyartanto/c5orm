<?php

/**
*  Concrete5 ORM
*  Inpired by Eloquent
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

use Database;

defined('C5_EXECUTE') or die('Access Denied.');

class Model
{

    /**
     * Get database object
     *
     * @return Concrete\Core\Database\Connection\Connection
     */
    public static function db()
    {
        return Database::get();
    }

    /**
     * Get table name
     *
     * @return string
     */
    public static function getTableName()
    {
        return static::$table;
    }

    /**
     * Get primary column name
     *
     * @return string
     */
    public static function getPrimaryColumn()
    {
        $primary = self::db()->GetRow("SHOW KEYS FROM " . self::getTableName() . " WHERE Key_name = 'PRIMARY'");
        return $primary['Column_name'] ?: null;
    }

    /**
     * Find record by primary key
     *
     * @param string|int $id
     * @return object
     */
    public static function find($id)
    {
        if ($primary = self::getPrimaryColumn()) {
            $db = self::db();
            $row = $db->GetRow("SELECT * FROM " . self::getTableName() . " WHERE `$primary` = ?", [$id]);
            if (!$row) {
                return null;
            }
            return self::toCalledClass($row);
        }
    }

    /**
     * Add / Update data by its primary key
     *
     * @return object
     */
    public function save()
    {
        if ($primary = self::getPrimaryColumn()) {
            $values = [];
            if ($primaryValue = $this->{$primary}) {
                $q = "UPDATE " . self::getTableName() . " SET ";
                $sets = [];
                foreach($this as $attr => $value) {
                    if ($attr == $primary) continue;
                    $sets[] = "{$attr} = ?";
                    $values[] = $value;
                }
                $q .= implode(", ", $sets) . " WHERE `{$primary}` = ?";
                $values[] = $primaryValue;
                if (self::db()->Execute($q, $values)) {
                    return $this;
                }
            } else {
                $q = "INSERT INTO " . self::getTableName() . " SET ";
                $sets = [];
                foreach($this as $attr => $value) {
                    $sets[] = "{$attr} = ?";
                    $values[] = $value;
                }
                $q .= implode(", ", $sets);
                if (self::db()->Execute($q, $values)) {
                    $this->{$primary} = self::db()->Insert_ID();
                    return $this;
                }
            }
        }
    }

    public function getBuilder()
    {
        return new Builder(self::getTableName(), get_called_class());
    }

    /**
     * Add query condition
     *
     * @param string $column
     * @param string $operator
     * @param string|int $value
     * @return Fudyartanto\C5orm\Builder
     */
    public static function where($column, $operator, $value)
    {
        return self::getBuilder()->where($column, $operator, $value);
    }

    /**
     * Get all records
     *
     * @return Fudyartanto\C5orm\Collections
     */
    public static function all()
    {
        $data = self::db()->GetAll("SELECT * FROM " . self::getTableName());
        return new Collections(
            array_map(function($v) {
                return self::toCalledClass($v);
            }, $data)
        );
    }

    /**
     * Add relation to be popuplated
     * 
     * @param string/array $relation
     * @return Fudyartanto\C5orm\Builder
     */
    public static function with($relation)
    {
        return self::getBuilder()->with($relation);
    }

    /**
     * Convert array to current called class
     *
     * @param array $attrs
     * @return object
     */
    public static function toCalledClass($attrs)
    {
        $className = '\\' . get_called_class();
        $class = new $className;
        foreach ($attrs as $attr => $value) {
            $class->{$attr} = $value;
        }
        return is_object($class) ? $class : null;
    }

    /**
     * Delete current data from database
     *
     * @return bool
     */
    public function delete()
    {
        $primary = self::getPrimaryColumn();
        return self::db()->Execute("DELETE FROM " . self::getTableName() . " WHERE {$primary} = ?", [$this->{$primary}]);
    }

    /**
     * Convert collections data to array
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this;
    }

    /**
     * Mass update
     *
     * @param array $array -> ['col' => 'value']
     */
    public function update($array)
    {
        foreach ($array as $col => $value) {
            $this->{$col} = $value;
        }
        return $this->save();
    }

    /**
     * Get table column names
     *
     * @return array
     */
    public static function getColumnListing()
    {
        $columns = self::db()->GetAll("SHOW COLUMNS FROM " . self::getTableName());
        return $columns ? array_map(function($column) {
            return $column['Field'];
        }, $columns) : [];
    }

    /**
     * Define has many relationships
     *
     * @param string $className
     * @param string $foreignkey
     * @param string $localKey
     * @return object Collections
     */
    public function hasMany($className, $foreignkey, $localKey)
    {
        return call_user_func_array([$className, "where"], [$foreignkey, "=", $this->$localKey])->get()->data();
    }

    /**
     * Define has one relationships
     *
     * @param string $className
     * @param string $foreignkey
     * @param string $localKey
     * @return object
     */
    public function hasOne($className, $foreignkey, $localKey)
    {
        return call_user_func_array([$className, "where"], [$foreignkey, "=", $this->$localKey])->first();
    }

    /**
     * Add select query
     *
     * @param string $column
     * @return Fudyartanto\C5orm\Builder
     */
    public static function select($column)
    {
        return self::getBuilder()->select($column);
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
    public static function join($table, $colLeft, $operator, $colRight)
    {
        return self::getBuilder()->join($table, $colLeft, $operator, $colRight);
    }

    /**
     * Add order by query
     *
     * @param string $column
     * @param string $direction ASC|DESC
     * @return Fudyartanto\C5orm\Builder
     */
    public static function orderBy($column, $direction)
    {
        return self::getBuilder()->orderBy($column, $direction);
    }
}
