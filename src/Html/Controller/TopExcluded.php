<?php

namespace Phperf\Xhprof\Html\Controller;


use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Entity\SymbolStat;
use Phperf\Xhprof\Entity\Symbol;
use Yaoi\Database;
use Yaoi\View\Table\HTML;

class TopExcluded extends Compare
{
    public function listTop()
    {
        $database = Database::getInstance();
        $esc = SymbolStat::columns();

        $statement = $database
            ->select(SymbolStat::table())
            ->select("SUM(?) AS total_wt, SUM(?) AS total_ct, COUNT(DISTINCT ?) AS runs, ?, ?",
                $esc->wallTime, $esc->count, $esc->runId,
                $esc->symbolId, Symbol::columns()->name)
            ->leftJoin('? ON ? = ?', Symbol::table(), Symbol::columns()->id, $esc->symbolId)
            ->groupBy(SymbolStat::columns()->symbolId)
            ->order('total_wt DESC')
            ->limit(50);


        $res = $statement->query();

        $table = new HTML();
        foreach ($res as $row) {
            $row['name'] = '<a href="?symbol=' . urlencode($row['name']) . '">' . $row['name'] . '</a>';
            unset($row['symbol_id']);
            $table->addRow($row);
        }


        $this->layout->pushMain($table)->render();
    }

}