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
     * Constructor
     *
     * @param array
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Convert collections data to array
     *
     * @return array
     */
    public function toArray()
    {
        return is_array($this->data) ? array_map(function($v) {
            return (array) $v;
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