<?php

namespace Phperf\Xhprof\Ui;


use Phperf\Xhprof\Command\Ui\Index;
use Yaoi\Command\Application;
use Yaoi\Command\Definition;

class App extends \Phperf\Xhprof\Cli\App
{
    public $index;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $commandDefinitions->index = Index::definition();
        parent::setUpCommands($definition, $commandDefinitions);

        // TODO: Implement setUpCommands() method.
    }

}