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
            if ($this->{$primary}) {
                // update
            } else {
                // add
            }
        }
    }
}
