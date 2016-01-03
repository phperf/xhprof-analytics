<?php

namespace Phperf\Xhprof;

use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Log;

class Import extends BaseClass
{
    private $symbols = array();
    private $symbolStats = array();
    private $run;

    /** @var  Run */
    public $groupRun;

    private function getSymbol($name) {
        $symbol = &$this->symbols[$name];
        if (null === $symbol) {
            $symbol = new Symbol();
            $symbol->name = $name;
            $symbol->findOrSave();
        }

        return $symbol;
    }

    private function getStat(Symbol $symbol, $exclusive = true) {
        $symbolStat = &$this->symbolStats[$exclusive . '_' . $symbol->name];
        if (null === $symbolStat) {
            $symbolStat = new SymbolStat();
            $symbolStat->runId = $this->run->id;
            $symbolStat->symbolId = $symbol->id;
            $symbolStat->isInclusive = !$exclusive;
        }

        return $symbolStat;
    }

    public $lastSample;

    public $combineRun = false;

    public function addRun($xhprofData, Run $run = null) {
        if (!$xhprofData) {
            return;
        }

        //Database::getInstance()->log(new Log('stdout'));


        if (null === $run) {
            $run = new Run();
            $run->ut = time();
        }
        $this->run = $run;


        $this->symbolStats = array();
        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;

        $run->addXhSample($xhprofData['main()']);
        $run->save();
        $this->getStat($this->getSymbol('main()'));//->addXhSample($xhprofData['main()']);

        $totalWallTime = 0;
        $totalCount = 0;
        foreach ($xhprofData as $key => $value) {
            if ('main()' === $key) {
                continue;
            }

            $this->lastSample = $value;

            $symbolNames = explode('==>', $key);
            $parentSymbol = $this->getSymbol($symbolNames[0]);
            $childSymbol = $this->getSymbol($symbolNames[1]);

            if ('main()' === $parentSymbol->name) {
                $totalWallTime += $value['wt'];
            }
            $totalCount += $value['ct'];

            /*
            $relatedStat = new RelatedStat();
            $relatedStat->runId = $run->id;
            $relatedStat->parentSymbolId = $parentSymbol->id;
            $relatedStat->childSymbolId = $childSymbol->id;
            $relatedStat->addXhSample($value);
            */

            $this->getStat($parentSymbol)->subXhSample($value);
            $this->getStat($childSymbol)->addXhSample($value);
            $this->getStat($childSymbol, false)->addXhSample($value);

            //$batchSaver->add($relatedStat);

            //$relatedStat->save();
            unset($parentSymbol, $childSymbol);
        }

        $this->getStat($this->getSymbol('main()'))->wallTime = $totalWallTime;
        $this->getStat($this->getSymbol('main()'))->count = $totalCount;


        //$batchSaver->flush();

        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;
        foreach ($this->symbolStats as $stat) {
            $batchSaver->add($stat);
        }
        $batchSaver->flush();


        /*
        $columns = SymbolStat::columns();
        Database::getInstance()
            ->update(SymbolStat::table()->schemaName)
            ->set('? = ? / ?, ? = ? / ?',
                $columns->wallTimePart,
                $columns->wallTime,
                $totalWallTime,
                $columns->countPart,
                $columns->count,
                $totalCount
            )
            ->where('? = ?', $columns->runId, $run->id)->query()->execute();
        */

        //die();

        $this->groupRun->wallTime += $totalWallTime;
        $this->groupRun->runs++;
        $this->groupRun->save();
    }
}