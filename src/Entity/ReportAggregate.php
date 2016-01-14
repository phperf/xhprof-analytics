<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;

class ReportAggregate extends Aggregate
{
    public $utCreated;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        parent::setUpColumns($columns);
        $columns->utCreated = Column::INTEGER + Column::TIMESTAMP;
    }


}