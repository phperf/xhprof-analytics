<?php
namespace Phperf\Xhprof\Service;


use Phperf\Xhprof\BatchSaver;
use Phperf\Xhprof\Entity\Aggregate;
use Phperf\Xhprof\Entity\Project;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\TagGroup;
use Phperf\Xhprof\Entity\TempRelatedStat;
use Phperf\Xhprof\Entity\TempRun;
use Phperf\Xhprof\Entity\TempSymbolStat;
use Phperf\Xhprof\Query\MergeRunRelatedStat;
use Phperf\Xhprof\Query\MergeRunSymbolStat;
use Yaoi\Cli\Console;
use Yaoi\Database;
use Yaoi\String\Expression;
use Yaoi\String\Parser;

class ProfileManager
{


    public $noSquash;
    public $project;
    public $tags;


    // DA SIRIUS BIZNES LOGEEK FOLLOWZ

    private $count;
    /** @var  Run */
    public $runInstance;
    private $index = 0;

    private $symbols = array();
    private $symbolStats = array();
    private $run;

    private function getSymbol($name)
    {
        $symbol = &$this->symbols[$name];
        if (null === $symbol) {
            $symbol = new Symbol();
            $symbol->name = $name;
            $symbol->findOrSave();
        }

        return $symbol;
    }

    private function getSymbolStat(Symbol $symbol, $exclusive = true)
    {
        $symbolStat = &$this->symbolStats[$exclusive . '_' . $symbol->name];
        if (null === $symbolStat) {
            $symbolStat = new TempSymbolStat();
            $symbolStat->runId = $this->run->id;
            $symbolStat->symbolId = $symbol->id;
            $symbolStat->isInclusive = !$exclusive;
        }

        return $symbolStat;
    }


    private $relatedStats = array();

    private function getRelatedStat(Symbol $parent, Symbol $child)
    {
        $relatedStat = &$this->relatedStats[$parent->name . '==>' . $child->name];
        if (null === $relatedStat) {
            $relatedStat = new TempRelatedStat();
            $relatedStat->runId = $this->run->id;
            $relatedStat->parentSymbolId = $parent->id;
            $relatedStat->childSymbolId = $child->id;
        }

        return $relatedStat;

    }

    public $lastSample;

    public function addRun($xhprofData, TempRun $run = null)
    {
        if (!$xhprofData) {
            return;
        }

        //Database::getInstance()->log(new Log('stdout'));

        if (null === $run) {
            $run = new TempRun();
            $run->ut = time();
        }
        $this->run = $run;

        $run->addXhSample($xhprofData['main()']);
        $run->save();

        $this->getSymbolStat($this->getSymbol('main()'), false)->addXhSample($xhprofData['main()']);

        $totalInclusive = $this->getSymbolStat($this->getSymbol('TOTAL'), false);
        foreach ($xhprofData as $key => $value) {
            if ('main()' === $key) {
                continue;
            }


            $this->lastSample = $value;

            $symbolNames = explode('==>', $key);

            $parentSymbol = $this->getSymbol($symbolNames[0]);
            $childSymbol = $this->getSymbol($symbolNames[1]);

            $totalInclusive->addXhSample($value, 'main()' === $parentSymbol->name); // add time only for level 1 calls

            $this->getRelatedStat($parentSymbol, $childSymbol)->addXhSample($value);

            $this->getSymbolStat($parentSymbol)->subXhSample($value);
            $this->getSymbolStat($childSymbol)->addXhSample($value);
            $this->getSymbolStat($childSymbol, false)->addXhSample($value);

            unset($parentSymbol, $childSymbol);
        }

        $run->calls += $totalInclusive->calls;
        $run->save();

        //$batchSaver->flush();

        if ($this->runInstance) {
            $this->runInstance->runs++;
        }
        if ($this->noSquash) {
            $this->saveStats();
        }
        return $run;
    }


    private function saveSymbolStats()
    {
        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;
        foreach ($this->symbolStats as $stat) {
            $batchSaver->add($stat);
        }
        $batchSaver->flush();
        $this->symbolStats = array();
    }


    private function saveRelatedStats()
    {
        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;
        foreach ($this->relatedStats as $stat) {
            $batchSaver->add($stat);
        }
        $batchSaver->flush();
        $this->relatedStats = array();
    }


    public function addData($filename, $content)
    {

        if (null === $this->runInstance) {
            $this->runInstance = new Run();
            $this->runInstance->ut = time();
            $this->runInstance->save();

            if ($this->project) {
                $project = new Project();
                $project->name = $this->project;
                $project->findOrSave();

                $this->runInstance->projectId = $project->id;
            }

            if ($this->tags) {
                $tagGroup = new TagGroup();
                $tagGroup->setTags($this->tags);
                $tagGroup->projectId = $this->runInstance->projectId;
                $tagGroup->findOrSave();

                $this->runInstance->tagGroupId = $tagGroup->id;
                $this->runInstance->save();
            }
        }

        ++$this->index;
        Console::getInstance()->returnCaret()->printF(new Expression('?% ? ?',
            round(100 * ($this->index / $this->count)), $this->index, $filename));

        $xhprofData = unserialize($content);
        if (!is_array($xhprofData)) {
            throw new \Phperf\Xhprof\Service\Exception('Can not unserialize ' . $filename);
        }

        $nameString = new Parser($filename);
        $ut = floor((string)$nameString->inner('_', '.serialized', true));

        $run = $this->runInstance;

        /*
        if ($run->findSaved() && $this->noSquash) {
            Console::getInstance()->printLine(" already imported");
            return;
        }
        */


        //xhprof_enable();
        $this->addRun($xhprofData, $run);
        //$data = xhprof_disable();
        //$xhFilename = '/tmp/xhprof/import' . '_' . microtime(1) . '.serialized';
        //file_put_contents($xhFilename, serialize($data));
    }


    public function saveStats()
    {
        $this->saveSymbolStats();
        $this->saveRelatedStats();
    }


    private function getAggregateRun($period, $startUt, $tagGroupId = null)
    {
        $aggregate = new Aggregate();
        $aggregate->period = $period;
        $aggregate->utFrom = $startUt;
        $aggregate->tagGroupId = $tagGroupId;
        $exist = $aggregate->findSaved();
        if (!$exist) {
            $aggregateRun = new Run();
            $aggregateRun->ut = $startUt;
            $aggregateRun->tagGroupId = $tagGroupId;
            $aggregateRun->projectId = null; // TODO properize
            $aggregateRun->save();
            $aggregate->runId = $aggregateRun->id;
            $aggregate->save();
        }
        else {
            $aggregateRun = Run::findByPrimaryKey($exist->runId);
        }
        return $aggregateRun;
    }

    public function addToAggregates(TempRun $run)
    {
        $time = $run->ut; // TODO process timezone
        $dates = array(
            //Aggregate::PERIOD_MINUTE => 60 * (int)($time / 60),
            //Aggregate::PERIOD_HOUR => 3600 * (int)($time / 3600),
            Aggregate::PERIOD_DAY => strtotime('today 00:00:00', $time),
            //Aggregate::PERIOD_WEEK => strtotime('monday this week 00:00:00', $time),
            //Aggregate::PERIOD_MONTH => strtotime('first day of this month 00:00:00', $time),
        );

        /** @var Run[] $destinations */
        $destinations = array();

        foreach ($dates as $period => $startUt) {
            if ($aggregateRun = $this->getAggregateRun($period, $startUt, null)) {
                $destinations [] = $aggregateRun;
            }
            if ($run->tagGroupId !== null) {
                if ($aggregateRun = $this->getAggregateRun($period, $startUt, $run->tagGroupId)) {
                    $destinations [] = $aggregateRun;
                }
            }
        }
        //ini_set('xdebug.var_display_max_depth', 5);
        //ini_set('xdebug.var_display_max_children', 256);
        //ini_set('xdebug.var_display_max_data', 10240);

        $mergeSymbolStat = new MergeRunSymbolStat($run, $destinations);
        //var_dump((string)$mergeSymbolStat->build());
        Database::getInstance()->query($mergeSymbolStat->build());
        $mergeRelatedStat = new MergeRunRelatedStat($run, $destinations);
        Database::getInstance()->query($mergeRelatedStat->build());

        foreach ($destinations as $destination) {
            $destination->wallTime += $run->wallTime;
            $destination->cpu += $run->cpu;
            $destination->calls += $run->calls;
            $destination->runs += $run->runs;
            $destination->memoryUsage = max($run->memoryUsage, $destination->memoryUsage);
            $destination->peakMemoryUsage = max($run->peakMemoryUsage, $destination->peakMemoryUsage);

            $destination->save();
        }
    }


}