<?php

namespace Phperf\Xhprof\Cli;

use Mishak\ArchiveTar\Reader;
use Phperf\Xhprof\BatchSaver;
use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\Command;
use Yaoi\Cli\Console;
use Yaoi\Cli\Option;
use Yaoi\Command\Definition;
use Yaoi\Database;
use Yaoi\Database\Exception;
use Yaoi\Log;
use Yaoi\String\Expression;
use Yaoi\String\Parser;
use Yaoi\String\StringValue;

class Import extends Command
{
    public $path;
    public $groupName;
    public $profilerNamespace;
    public $allowDuplicates;
    public $squash;


    private $count;
    /** @var  Run */
    private $groupRun;
    private $index = 0;

    private function addData($filename, $content)
    {
        try {

            if (null === $this->groupRun) {
                $this->groupRun = new Run();
                $this->groupRun->ut = time();
                $this->groupRun->name = $this->groupName;
                $this->groupRun->save();
            }



            ++$this->index;
            Console::getInstance()->returnCaret()->printF(new Expression('?% ? ?',
                round(100 * ($this->index / $this->count)), $this->index, $filename));

            $xhprofData = unserialize($content);
            if (!is_array($xhprofData)) {
                $this->response->error(new Expression("Can not unserialize ?", $filename));
                return;
            }

            $nameString = new Parser($filename);
            $ut = floor((string)$nameString->inner('_', '.serialized', true));

            if ($this->squash) {
                $run = $this->groupRun;
            }
            else {
                $run = new Run();
                $run->name = $filename;
                $run->ut = $ut;
            }

            if ($run->find() && !$this->squash) {
                Console::getInstance()->printLine(" already imported");
                return;
            }


            //xhprof_enable();
            $this->addRun($xhprofData, $run);
            //$data = xhprof_disable();
            //$xhFilename = '/tmp/xhprof/import' . '_' . microtime(1) . '.serialized';
            //file_put_contents($xhFilename, serialize($data));
        } catch (Exception $exception) {
            Console::getInstance()->eol();
            print_r($this->lastSample);
            Console::getInstance()->printLine($exception->query);
            $this->response->error($exception->getMessage());
        }

    }

    public function performAction()
    {
        if (!file_exists($this->path)) {
            $this->response->error(new Expression('Path ? not found', $this->path));
            return;
        }


        if (StringValue::create($this->path)->ends('.tar.gz')) {
            $filename = $this->path;
            $reader = new Reader($filename);
            $reader->setReadContents(false);
            $reader->setBuffer(1000000);
            $count = 0;
            foreach ($reader as $record) {
                if (in_array($record['type'], array(Reader::REGULAR, Reader::AREGULAR), TRUE)) {
                    //var_dump($record['filename']);
                    ++$count;
                }
            }
            $reader->setReadContents(true);
            $this->count = $count;


            foreach ($reader as $record) {
                if (in_array($record['type'], array(Reader::REGULAR, Reader::AREGULAR), TRUE)) {
                    $this->addData($this->path . ':' . trim($record['filename']), $record['contents']);
                }
            }
        } else {
            $profiles = array();
            if (is_dir($this->path)) {
                if ($handle = opendir($this->path)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                            $profiles [] = $this->path . '/' . $entry;
                        }
                    }
                    closedir($handle);
                }
            } else {
                $profiles[] = $this->path;
            }

            $this->count = count($profiles);


            foreach ($profiles as $filename) {
                $this->addData($filename, file_get_contents($filename));
            }
        }

        if ($this->squash) {
            $this->saveSymbolStats();
            $this->saveRelatedStats();
        }
    }

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->path = Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->setDescription('Path to profiles directory or file');

        $options->groupName = Option::create()
            ->setIsUnnamed()
            ->setDescription('Profiles session/group name');

        $options->profilerNamespace = Option::create()
            ->setIsUnnamed()
            ->setDescription('WTF?');

        $options->allowDuplicates = Option::create()
            ->setShortName('d')
            ->setDescription('Allow duplicate profiles (by file path)');

        $options->squash = Option::create()
            ->setDescription('Combine all data in one run for multiple profiles');

        $definition->name = 'xhprof-import';
        $definition->description = 'XHPROF data importing tool';
        $definition->version = 'v0.1';
    }

    public static function info($message, $binds = null)
    {
        if (null !== $binds) {
            $message = (string)Expression::create(func_get_args());
        }
        Console::getInstance()->printLine($message);
    }



    // DA SIRIUS BIZNES LOGEEK FOLLOWZ

    private $symbols = array();
    private $symbolStats = array();
    private $run;

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


    private $relatedStats = array();
    private function getRelatedStat(Symbol $parent, Symbol $child) {
        $relatedStat = &$this->relatedStats[$parent->name . '==>' . $child->name];
        if (null === $relatedStat) {
            $relatedStat = new RelatedStat();
            $relatedStat->runId = $this->run->id;
            $relatedStat->parentSymbolId = $parent->id;
            $relatedStat->childSymbolId = $child->id;
        }

        return $relatedStat;

    }

    private $lastSample;

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

        $run->addXhSample($xhprofData['main()']);
        $run->save();

        $this->getStat($this->getSymbol('main()'), false)->addXhSample($xhprofData['main()']);

        $totalInclusive = $this->getStat($this->getSymbol('TOTAL'), false);
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

            $this->getStat($parentSymbol)->subXhSample($value);
            $this->getStat($childSymbol)->addXhSample($value);
            $this->getStat($childSymbol, false)->addXhSample($value);

            unset($parentSymbol, $childSymbol);
        }

        //$batchSaver->flush();

        $this->groupRun->runs++;
        if (!$this->squash) {
            $this->saveRelatedStats();
            $this->saveSymbolStats();
        }
    }



    private function saveSymbolStats() {
        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;
        foreach ($this->symbolStats as $stat) {
            $batchSaver->add($stat);
        }
        $batchSaver->flush();
        $this->symbolStats = array();
    }


    private function saveRelatedStats() {
        $batchSaver = new BatchSaver();
        $batchSaver->pageSize = 1000;
        foreach ($this->relatedStats as $stat) {
            $batchSaver->add($stat);
        }
        $batchSaver->flush();
        $this->relatedStats = array();
    }
}

