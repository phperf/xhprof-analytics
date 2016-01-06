<?php

namespace Phperf\Xhprof\Html\Controller;



use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Entity\SymbolStat;
use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Yaoi\Database;
use Yaoi\View\Raw;
use Yaoi\View\Stack;
use Yaoi\View\Table\HTML;

class SymbolInfo extends Compare
{
    public function showSymbol() {

        if (!empty($_GET['run'])) {
            $run = new Run();
            $run->name = $_GET['run'];
            $run->findSaved()->id;
            $this->runName = $run->findSaved()->id;
        }

        /** @var Symbol $symbol */
        $symbol = Symbol::statement()->where('? = ?', Symbol::columns()->name, $this->symbol)->query()->fetchRow();
        if (null === $symbol) {
            return $this->error('Symbol ? not found', $this->symbol);
        }

        $stack = new Stack();

        $database = Database::getInstance();
        $inc = RelatedStat::columns();

        $mainSymbol = new Symbol();
        $mainSymbol->name = 'main()';
        $mainSymbol->findOrSave();

        $total_wt = $database->select(Run::table())
            ->select("SUM(?) AS total_wt", Run::columns()->wallTime)
            ->query()->fetchRow('total_wt');

        $stack->push(Raw::create('<h2>Total: ' . $total_wt . '</h2>'));

        $statement = $database
            ->select(RelatedStat::table())
            ->select("SUM(?)/1000000 AS total_wt, SUM(?) AS total_ct, COUNT(DISTINCT ?) AS runs, ? AS symbol_id, ?",
                $inc->wallTime, $inc->count, $inc->runId,
                $inc->parentSymbolId, Symbol::columns()->name)
            ->leftJoin('? ON ? = ?', Symbol::table(), Symbol::columns()->id, $inc->parentSymbolId)
            ->where('? = ?', $inc->childSymbolId, $symbol->id)
            //->where('? = ?', $inc->runId, $this->runId)
            ->groupBy($inc->parentSymbolId)
            ->order('total_wt DESC')
            ->limit(50);


        $res = $statement->query();

        $table = new HTML();
        $stack->push(Raw::create('<h2>Parents</h2>'));
        $stack->push($table);
        foreach ($res as $row) {
            $row['percent'] = round(100 * $row['total_wt'] / $total_wt, 2);
            $row['name'] = '<a href="?symbol=' . urlencode($row['name']) . '&run='.$_GET['run'].'">' . $row['name'] . '</a>';
            unset($row['symbol_id']);
            $table->addRow($row);
        }



        $statement = $database
            ->select(RelatedStat::table())
            ->select("SUM(?)/1000000 AS total_wt, SUM(?) AS total_ct, COUNT(DISTINCT ?) AS runs, ? AS symbol_id, ?",
                $inc->wallTime, $inc->count, $inc->runId,
                $inc->childSymbolId, Symbol::columns()->name)
            ->leftJoin('? ON ? = ?', Symbol::table(), Symbol::columns()->id, $inc->childSymbolId)
            ->where('? = ?', $inc->parentSymbolId, $symbol->id)
            //->where('? = ?', $inc->runId, $this->runId)
            ->groupBy($inc->childSymbolId)
            ->order('total_wt DESC')
            ->limit(50);


        $res = $statement->query();

        $table = new HTML();
        $stack->push(Raw::create('<h2>Children</h2>'));
        $stack->push($table);
        foreach ($res as $row) {
            $row['percent'] = round(100 * $row['total_wt'] / $total_wt, 2);
            $row['name'] = '<a href="?symbol=' . urlencode($row['name']) . '&run='.$_GET['run'].'">' . $row['name'] . '</a>';
            unset($row['symbol_id']);
            $table->addRow($row);
        }


        $this->layout->setTitle($symbol->name)->pushMain($stack)->render();

    }
}