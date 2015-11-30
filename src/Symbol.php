<?php

namespace Phperf\Xhprof;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Symbol extends Entity
{
    public $id;
    public $name;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::create(Column::STRING)->setUnique();
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
    }

}