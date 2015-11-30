<?php
namespace Phperf\Xhprof;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Group extends Entity
{
    public $id;
    public $name;
    public $ut;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::STRING;
        $columns->ut = Column::INTEGER;
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        // TODO: Implement setUpTable() method.
    }

}