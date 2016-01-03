<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class RunTag extends Entity
{
    public $runId;
    public $tagId;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->runId = Run::columns()->id;
        $columns->tagId = Tag::columns()->id;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('phperf_xhprof_run_tag');
        $table->setPrimaryKey($columns->runId, $columns->tagId);
    }


}