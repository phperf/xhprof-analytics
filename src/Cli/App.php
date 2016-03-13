<?php

namespace Phperf\Xhprof\Cli;



use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\CreateProject;
use Phperf\Xhprof\Command\Runs;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class App extends Application
{
    public $runs;
    public $compare;
    public $import;
    public $createProject;
    public $migrate;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->runs = Runs::definition();
        $commandDefinitions->compare = Compare::definition();
        $commandDefinitions->import = Import::definition();
        $commandDefinitions->createProject = CreateProject::definition();
        $commandDefinitions->migrate = Migrate::definition();

        $definition->name = 'xha';
        $definition->description = 'XHPROF analytics tool';
    }


}