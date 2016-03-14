<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Project extends Entity
{
    public $id;
    public $apiKey;
    public $name;
    public $description;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->apiKey = Column::STRING;
        $columns->name = Column::create(Column::STRING)->setUnique();
        $columns->description = Column::STRING;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
    }

}