<?php

namespace Phperf\Xhprof\Report;


use Phperf\Xhprof\Database\Result;

class StatData extends Result
{
    public $totalWt;
    public $totalCt;
    public $runs;
    public $name;

    public static function statement($filter = null) {

    }

    public function get() {
        $statement = $database
            ->select(RelatedStat::table())
            ->select("SUM(?) AS total_wt, SUM(?) AS total_ct, COUNT(DISTINCT ?) AS runs, ?",
                $inc->wallTime, $inc->count, $inc->runId,
                $inc->childSymbolId, Symbol::columns()->name)
            ->leftJoin('? ON ? = ?', Symbol::table(), Symbol::columns()->id, $inc->childSymbolId)
            ->where('? = ?', $inc->parentSymbolId, $symbol->id)
            ->groupBy($inc->childSymbolId)
            ->order('total_wt DESC')
            ->limit(50);

    }



    public function find() {

    }
}