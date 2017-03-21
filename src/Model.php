<?php

/**
*  Concrete5 ORM
*  Inpired by Eloquent
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

use Database;

class Model
{
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

            $className = '\\' . get_called_class();
            $class = new $className;
            foreach ($row as $attr => $value) {
                $class->{$attr} = $value;
            }
            return is_object($class) ? $class : null;
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
                return self::db()->Execute($q, $values);
            } else {
                $q = "INSERT INTO " . self::getTableName() . " SET ";
                $sets = [];
                foreach($this as $attr => $value) {
                    $sets[] = "{$attr} = ?";
                    $values = $value;
                }
                $q .= implode(", ", $sets);
                return self::db()->Execute($q, $values);
            }
        }
    }

    /**
     * Add query condition
     *
     * @param string $column
     * @param string $operator
     * @param string|int $value
     */
    public static function where($column, $operator, $value)
    {
        return (new Builder(self::getTableName()))->where($column, $operator, $value);
    }
}
