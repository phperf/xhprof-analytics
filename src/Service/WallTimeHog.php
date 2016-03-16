<?php

namespace Phperf\Xhprof\Service;


use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\Database;

class WallTimeHog
{
    private $threshold = 0.1;

    /** @var Run */
    private $run;

    public function __construct(Run $run)
    {
        $this->run = $run;
    }

    /** @var Trace[] */
    private $traces;

    private $symbolsFound = array();

    /**
     * @return array|Trace[]
     */
    public function getTraces()
    {
        if (null === $this->traces) {
            $this->traces = array();
            $this->topInclusive();
        }
        return $this->traces;
    }

    private function topInclusive()
    {
        $symbolStat = $this->run->symbolStat();
        $symbolCols = $symbolStat->columns();
        /** @var SymbolStat[] $topInclusive */
        $topInclusive = $symbolStat->statement()
            ->where('? = ?', $symbolCols->runId, $this->run->id)
            ->where('?', $symbolCols->isInclusive)
            ->where('? > ?', $symbolCols->wallTime, $this->threshold * $this->run->wallTime)
            ->order('? DESC', $symbolCols->wallTime)
            ->query()
            ->fetchAll();

        foreach ($topInclusive as $stat) {
            if (isset($this->symbolsFound[$stat->symbolId])) {
                continue;
            }
            $this->symbolsFound[$stat->symbolId] = 1;

            $trace = new Trace(Symbol::findByPrimaryKey($stat->symbolId)->name, $stat);
            if ($trace->symbol === 'TOTAL') {
                continue;
            }
            if ($trace->symbol !== 'main()') {
                continue;
            }

            //var_dump($trace);
            //$this->topChildren($stat->symbolId, $this->threshold * $stat->wallTime, $trace);
            $this->topChildren($stat->symbolId, $this->threshold * $this->run->wallTime, $trace);
            $this->traces[] = $trace;
        }
    }


    private function topChildren($parentSymbolId, $threshold, Trace $trace)
    {
        $relatedStat = $this->run->relatedStat();
        $relCols = $relatedStat->columns();
        /** @var RelatedStat[] $topChildren */
        $topChildren = $relatedStat->statement()
            ->where('? = ?', $relCols->parentSymbolId, $parentSymbolId)
            ->where('? = ?', $relCols->runId, $this->run->id)
            ->where('? > ?', $relCols->wallTime, $threshold)
            ->order('? DESC', $relCols->wallTime)
            ->limit(5)
            ->query()
            ->fetchAll();

        foreach ($topChildren as $stat) {
            //if (isset($this->symbolsFound[$stat->childSymbolId])) {
            //    continue;
            //}
            $this->symbolsFound[$stat->childSymbolId] = 1;

            $childTrace = new Trace(Symbol::findByPrimaryKey($stat->childSymbolId)->name, $stat);
            //var_dump($childTrace);
            $trace->children[] = $childTrace;
            //$this->topChildren($stat->childSymbolId, $this->threshold * $stat->wallTime, $childTrace);
            $this->topChildren($stat->childSymbolId, $this->threshold * $this->run->wallTime, $childTrace);
        }
    }

}