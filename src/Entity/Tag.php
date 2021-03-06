<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Tag extends Entity
{
    public $id;
    public $type;
    public $text;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->type = Column::STRING;
        $columns->text = Column::STRING + Column::NOT_NULL;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('phperf_xhprof_tag');
    }




}