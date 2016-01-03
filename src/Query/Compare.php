<?php

namespace Phperf\Xhprof\Query;

use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Yaoi\Sql\Symbol as S;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\BaseClass;
use Yaoi\Database;

class Compare extends BaseClass
{


    private function topSymbolsExpr() {
        $cols = SymbolStat::columns();
        $expr = SymbolStat::statement()
            ->select('?', $cols->symbolId)
            ->where('? IN (?)', $cols->runId, $this->runIds)
            ->where('? = ?', $cols->isInclusive, $this->isInclusive)
            ->groupBy($cols->symbolId);

        return $expr;
    }

    /** @var Run[] */
    private $runs = array();
    private $runIds = array();


    public function addRun($runName) {
        if (!array_key_exists($runName, $this->runs)) {
            $run = Run::statement()->select('*')
                ->where('? = ?', Run::columns()->name, $runName)
                ->query()->fetchRow();
            $this->runs[$runName] = $run;
            $this->runIds []= Run::cast($run)->id;
        }
    }

    public $isInclusive = 1;

    /**
     * @return $this|\Yaoi\Sql\SelectInterface|\Yaoi\Sql\UpdateInterface
     */
    public function build() {
        $symbolsDerived = SymbolStat::table('s');
        $symbolsDerivedCols = SymbolStat::columns($symbolsDerived);

        $symbolCols = Symbol::columns();

        $expr = Database::getInstance()
            ->statement()
            ->select('? AS ?', $symbolCols->name, new S('function'))
            ->from('? AS s', $this->topSymbolsExpr())
            ->leftJoin('? ON ? = ?', Symbol::table(), $symbolsDerivedCols->symbolId, $symbolCols->id)
            ->groupBy('?', $symbolsDerivedCols->symbolId);

        $avgWtSymbols = array();
        foreach ($this->runs as $run) {
            $run1Table = SymbolStat::table($run->name);
            $run1Cols = SymbolStat::columns($run1Table);
            $awgWtSymbol = new S($run->name . '_wtms_avg');
            $avgWtSymbols []= $awgWtSymbol;

            $expr->select('0.001 * ? / ? AS ?', $run1Cols->wallTime, $run->count, $awgWtSymbol);
            $expr->select('0.001 * ? / ? AS ?', $run1Cols->cpu,$run->count, new S($run->name . '_cpums_avg'));
            $expr->select('? AS ?', $run1Cols->runs, new S($run->name . '_runs'));
            $expr->select('? AS ?', $run1Cols->count, new S($run->name . '_calls'));
            $expr->select('0.001 * ? / ? AS ?', $run1Cols->wallTime, $run1Cols->count, new S($run->name . '_call_wt'));
            $expr->select('100 * ? / ? AS ?', $run1Cols->wallTime, $run->wallTime, new S($run->name . '_wt_percent'));
            $expr->leftJoin('? ON ? = ? AND ? = ? AND ? = ?',
                $run1Table,
                $run1Cols->symbolId, $symbolsDerivedCols->symbolId,
                $run1Cols->isInclusive, $this->isInclusive,
                $run1Cols->runId, $run->id
            );
        }


        $outerExpr = Database::getInstance()->statement()->select('*')->from('? AS ss', $expr)
            ->order('COALESCE(null, ?) DESC', array($avgWtSymbols))->limit(10);

        return $outerExpr;
    }

}