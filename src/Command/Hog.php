<?php

namespace Phperf\Xhprof\Command;

use Phperf\Xhprof\Entity\Exception;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Service\Trace;
use Phperf\Xhprof\Service\WallTimeHog;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Content\Info;

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

        //$this->response->addContent('<pre>' . print_r($traces, 1) . '</pre>');
    }


    private function echoTrace(Trace $trace, $padding = '') {
        $this->response->addContent(new Info($padding . ' '. $trace->symbol . ' ' . round(100 * $trace->wallTime, 2) . '%'));
        foreach ($trace->children as $child) {
            $this->echoTrace($child, $padding . '-');
        }
        //$this->response->addContent('<hr />');
    }

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->runId = Command\Option::create()->setType()->setIsRequired()->setDescription('Run id');
    }


}