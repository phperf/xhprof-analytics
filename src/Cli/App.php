<?php

namespace Phperf\Xhprof\Cli;



use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\CreateProject;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class App extends Application
{
    public $compare;
    public $import;
    public $createProject;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->compare = Compare::definition();
        $commandDefinitions->import = Import::definition();
        $commandDefinitions->createProject = CreateProject::definition();

        $definition->name = 'xha';
        $definition->description = 'XHPROF analytics tool';
    }


}