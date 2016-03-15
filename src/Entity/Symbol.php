<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Symbol extends Entity
{
    public $id;
    public $name;

    static function setUpColumns($columns)
    {
        $columns->id = Column::INTEGER + Column::UNSIGNED;
        $columns->name = Column::create(Column::STRING)->setUnique();
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setPrimaryKey($columns->id);
    }

}