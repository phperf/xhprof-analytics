<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class Aggregate extends Entity
{
    const PERIOD_NONE = 0;
    const PERIOD_MINUTE = 1;
    const PERIOD_HOUR = 2;
    const PERIOD_DAY = 3;
    const PERIOD_MONTH = 4;

    public $id;
    public $runId;
    public $tagGroupId;
    public $period;
    public $utFrom;
    public $utTo;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->runId = Run::columns()->id;
        $columns->tagGroupId = TagGroup::columns()->id;
        $columns->period = Column::INTEGER + Column::NOT_NULL;
        $columns->utFrom = Column::INTEGER + Column::TIMESTAMP;
        $columns->utTo = Column::INTEGER + Column::TIMESTAMP;
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


    public function merge(Run $run)
    {

    }

}