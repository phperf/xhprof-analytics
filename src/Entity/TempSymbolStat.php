<?php

namespace Phperf\Xhprof\Entity;


class TempSymbolStat extends SymbolStat
{
    static function setUpColumns($columns)
    {
        parent::setUpColumns($columns);
        $columns->runId = TempRun::columns()->id;
    }


}