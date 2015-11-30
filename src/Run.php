<?php

namespace Phperf\Xhprof;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Run extends Stat
{
    public $id;
    public $parentId;
    public $ut;
    public $name;
    public $runs;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->ut = Column::INTEGER;
        $columns->parentId = Column::INTEGER;
        $columns->runs = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->name = Column::STRING;

        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        if ($columns->parentId->flags & Column::NOT_NULL) {
            $columns->parentId->flags -= Column::NOT_NULL;
        }
    }

}