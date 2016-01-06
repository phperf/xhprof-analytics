<?php

namespace Phperf\Xhprof\Query;

use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Yaoi\Sql\Statement;
use Yaoi\Sql\Symbol as S;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\BaseClass;
use Yaoi\Database;

class Compare extends BaseClass
{


    public function topSymbolsExpr() {
        $cols = SymbolStat::columns();
        $expr = SymbolStat::statement()
            ->select('?', $cols->symbolId)
            ->where('? IN (?)', $cols->runId, $this->runIds)
            ->where('? = ?', $cols->isInclusive, $this->isInclusive)
            ->groupBy($cols->symbolId);


        if ($this->symbolId) {
            $expr->where('? = ?', $cols->symbolId, $this->symbolId);
        }

        return $expr;
    }

    public function topParentsExpr()
    {
        $cols = RelatedStat::columns();
        $expr = RelatedStat::statement()
            ->select('? AS symbol_id', $cols->parentSymbolId)
            ->where('? IN (?)', $cols->runId, $this->runIds)
            ->where('? = ?', $cols->childSymbolId, $this->symbolId)
            ->groupBy($cols->parentSymbolId);

        return $expr;
    }

    public function topChildrenExpr()
    {
        $cols = RelatedStat::columns();
        $expr = RelatedStat::statement()
            ->select('? AS symbol_id', $cols->childSymbolId)
            ->where('? IN (?)', $cols->runId, $this->runIds)
            ->where('? = ?', $cols->parentSymbolId, $this->symbolId)
            ->groupBy($cols->childSymbolId);

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

    public function setSymbol($symbolName) {
        if (null === $symbolName) {
            $this->symbolId = null;
        }

        $symbol = Symbol::statement()->where('? = ?', Symbol::columns()->name, $symbolName)->query()->fetchRow();
        if (!$symbol) {
            throw new \Exception('Symbol not found');
        }

        $this->symbolId = Symbol::cast($symbol)->id;
    }

    public $isInclusive = 1;
    public $limit = 10;
    private $symbolId;

    /** @var  Statement */
    protected $expr;

    protected $avgWtSymbols;

    protected function addColumns($run, $runXCols) {
        $awgWtSymbol = new S($run->name . '_wtms_avg');
        $this->avgWtSymbols [] = $awgWtSymbol;

        $this->expr->select('0.001 * ? / ? AS ?', $runXCols->wallTime, $run->count, $awgWtSymbol);
        $this->expr->select('0.001 * ? / ? AS ?', $runXCols->cpu, $run->count, new S($run->name . '_cpums_avg'));
        $this->expr->select('? AS ?', $runXCols->runs, new S($run->name . '_runs'));
        $this->expr->select('? AS ?', $runXCols->count, new S($run->name . '_calls'));
        $this->expr->select('0.001 * ? / ? AS ?', $runXCols->wallTime, $runXCols->count, new S($run->name . '_call_wt'));
        $this->expr->select('100 * ? / ? AS ?', $runXCols->wallTime, $run->wallTime, new S($run->name . '_wt_percent'));
    }


    /**
     * @return $this|\Yaoi\Sql\SelectInterface|\Yaoi\Sql\UpdateInterface
     */
    public function build($statExpr) {
        $symbolsDerived = SymbolStat::table('s');
        $symbolsDerivedCols = SymbolStat::columns($symbolsDerived);

        $symbolCols = Symbol::columns();

        $this->expr = Database::getInstance()
            ->statement()
            ->select('? AS ?', $symbolCols->name, new S('function'))
            ->from('? AS s', $statExpr)
            ->leftJoin('? ON ? = ?', Symbol::table(), $symbolsDerivedCols->symbolId, $symbolCols->id)
            ->groupBy('?', $symbolsDerivedCols->symbolId);

        $this->avgWtSymbols = array();
        foreach ($this->runs as $run) {
            $runXTable = SymbolStat::table($run->name);
            $runXCols = SymbolStat::columns($runXTable);


            $this->addColumns($run, $runXCols);

            $this->expr->leftJoin('? ON ? = ? AND ? = ? AND ? = ?',
                $runXTable,
                $runXCols->symbolId, $symbolsDerivedCols->symbolId,
                $runXCols->isInclusive, $this->isInclusive,
                $runXCols->runId, $run->id
            );
        }


        $outerExpr = Database::getInstance()->statement()->select('*')->from('? AS ss', $this->expr)
            ->order('COALESCE(null, ?) DESC', array($this->avgWtSymbols))->limit($this->limit);

        return $outerExpr;
    }

}