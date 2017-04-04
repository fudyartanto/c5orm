<?php

/**
*  Concrete5 ORM
*  Inpired by Eloquent
*
*  @author  Arfan Fudyartanto <arfan@mylits.com>
*/
namespace Fudyartanto\C5orm;

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
}