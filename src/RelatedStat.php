<?php

namespace Phperf\Xhprof;

use Yaoi\Database\Entity;

class RelatedStat extends Stat
{
    public $parentSymbolId;
    public $childSymbolId;
    public $runId;


    static function setUpColumns($columns)
    {
        $columns->parentSymbolId = Symbol::columns()->id;
        $columns->childSymbolId = Symbol::columns()->id;
        $columns->runId = Run::columns()->id;
        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setPrimaryKey($columns->parentSymbolId, $columns->childSymbolId, $columns->runId);
    }

}