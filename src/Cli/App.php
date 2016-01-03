<?php

namespace Phperf\Xhprof\Cli;



use Phperf\Xhprof\Cli\Import;
use Phperf\Xhprof\Command\Compare;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class App extends Application
{
    public $compare;
    public $import;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->compare = Compare::definition();
        $commandDefinitions->import = Import::definition();

        $definition->name = 'xha';
        $definition->description = 'XHPROF analytics tool';
    }


}