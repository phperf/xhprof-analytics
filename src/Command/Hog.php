<?php

namespace Phperf\Xhprof\Command;

use Phperf\Xhprof\Entity\Exception;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Service\Trace;
use Phperf\Xhprof\Service\WallTimeHog;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Badge;
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
            foreach ($flatTrace as $data) {
                $compare->symbol = $data[0];
                $compare->runs = array($this->runId);


                $list->addItem(new Anchor(
                    Multiple::create()
                        ->addItem($data[0])
                        ->addItem(' ')
                        ->addItem(Badge::create(round(100 * $data[1], 2))),
                    $this->io->makeAnchor($compare))
                );
            }
            $this->response->addContent($list);
        }


        //$this->response->addContent('<pre>' . print_r($traces, 1) . '</pre>');
    }


    private $flatTraces = array();

    private function echoTrace(Trace $trace, $padding = '', $thisFlat = array()) {
        //$this->response->addContent(new Info($padding . ' '. $trace->symbol . ' ' . round(100 * $trace->wallTime, 2) . '%'));

        $thisFlat []= array($trace->symbol, $trace->wallTime);
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