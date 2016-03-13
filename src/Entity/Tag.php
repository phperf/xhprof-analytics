<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Index;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Tag extends Entity
{
    public $id;
    public $text;
    public $name;
    public $value;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->text = Column::STRING + Column::NOT_NULL;
        $columns->name = Column::STRING + Column::NOT_NULL;
        $columns->value = Column::STRING;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->addIndex(Index::TYPE_UNIQUE, $columns->text);
        $table->setSchemaName('phperf_xhprof_tag');
    }




}