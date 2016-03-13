<?php

namespace Phperf\Xhprof\Api;


use Phperf\Xhprof\Cli\Import;
use Phperf\Xhprof\Command\Compare;
use Yaoi\Command;
use Yaoi\Command\Definition;

class Application extends Command
{
    public $action;
    public $outputFormat;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->action = Command\Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->addToEnum(Import::definition());

        $options->outputFormat = Command\Option::create()->setType();
    }

    public function performAction()
    {
        var_dump($this->action);
        // TODO: Implement performAction() method.
    }


}