<?php

/**
*  Concrete5 ORM
*  Inpired by Eloquent
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

defined('C5_EXECUTE') or die('Access Denied.');

class Collections
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $with;

    /**
     * Constructor
     *
     * @param array
     */
    public function __construct($data, $with = [])
    {
        $this->data = $data;
        $this->with = $with;
    }

    /**
     * Convert collections data to array
     *
     * @return array
     */
    public function toArray()
    {
        return is_array($this->data) ? array_map(function($v) {
            $return = (array) $v;
            if ($this->with) {
                foreach ($this->with as $relation) {
                    $result = call_user_func(array($v, $relation));
                    if ($result) {
                        $result = array_map(function ($v) {
                            return (array) $v;
                        }, $result);
                        $return[$relation] = $result;
                    }
                }
            }
        }, $this->data) : [];
    }

    /**
     * Get data
     *
     * @return array
     */
    public function data()
    {
        return $this->data;
    }
}