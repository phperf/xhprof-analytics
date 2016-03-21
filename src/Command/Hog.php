<?php

namespace Phperf\Xhprof\Command;

use Phperf\Xhprof\Entity\Exception;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Service\Trace;
use Phperf\Xhprof\Service\WallTimeHog;
use Phperf\Xhprof\Ui\Formatter;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Content\Anchor;
use Yaoi\Twbs\Io\Content\Badge;
use Yaoi\Io\Content\ItemList;
use Yaoi\Io\Content\Multiple;

class Hog extends Command
{
    public $runId;

    public function performAction()
    {
        $run = Run::findByPrimaryKey($this->runId);
        if (!$run) {
            throw new Exception('Run not found');
        }

        $hog = new WallTimeHog($run);
        $traces = $hog->getTraces();

        foreach ($traces as $trace) {
            $this->echoTrace($trace);
        }

        //var_dump($this->flatTraces);

        $compare = Compare::createState();
        foreach ($this->flatTraces as $flatTrace) {
            array_shift($flatTrace);

            $list = new ItemList();
            foreach ($flatTrace as $trace) {
                $compare->symbol = $trace->symbol;
                $compare->runs = array($this->runId);


                $list->addItem(
                    Multiple::create()
                        ->addItem(new Anchor($trace->symbol, $this->io->makeAnchor($compare)))
                        ->addItem(' ')
                        ->addItem(Badge::create('wt: ' . round(100 * ($trace->stat->wallTime / $run->wallTime), 2) . '%'))
                        ->addItem(' ')
                        ->addItem(Badge::create('cpu: ' . round(100 * ($trace->stat->cpu / $run->cpu), 2) . '%'))
                        ->addItem(' ')
                        ->addItem(Badge::create('calls: ' . $trace->stat->calls))
                        ->addItem(' ')
                        ->addItem(Badge::create('mu: ' . Formatter::bytes($trace->stat->memoryUsage)))
                );
            }
            $this->response->addContent($list);
        }


        //$this->response->addContent('<pre>' . print_r($traces, 1) . '</pre>');
    }


    /** @var Trace[][] */
    private $flatTraces = array();

    private function echoTrace(Trace $trace, $padding = '', $thisFlat = array()) {
        //$this->response->addContent(new Info($padding . ' '. $trace->symbol . ' ' . round(100 * $trace->wallTime, 2) . '%'));

        $thisFlat []= $trace;
        if (!$trace->children) {
            $this->flatTraces []= $thisFlat;
        }
        else {
            foreach ($trace->children as $child) {
                $this->echoTrace($child, $padding . '-', $thisFlat);
            }
        }
    }

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->runId = Command\Option::create()->setType()->setIsRequired()->setDescription('Run id');
    }


}