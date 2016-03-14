<?php

namespace Phperf\Xhprof\Entity;


class TempRelatedStat extends RelatedStat
{
    static function setUpColumns($columns)
    {
        parent::setUpColumns($columns);
        $columns->runId = TempRun::columns()->id;
    }


}