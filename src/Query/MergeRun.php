<?php

namespace Phperf\Xhprof\Query;


use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\BaseClass;
use Yaoi\Sql\Batch;
use Yaoi\Sql\Statement;

class MergeRun extends BaseClass
{
    public function symbolStatExpr()
    {
    }

    public function build()
    {
        $expr = new Batch();
    }
}