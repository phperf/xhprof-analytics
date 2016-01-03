<?php

namespace Phperf\Xhprof\Api;


use Phperf\Xhprof\Command\Compare;
use Yaoi\Command\Definition;

class Application extends \Yaoi\Command\Application
{
    public $compare;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions)
    {
        $definition->name = 'xhprof-analytics-api';
        $definition->description = 'XHPROF Analytics API';
        $definition->version = 'v0.1';

        $commandDefinitions->compare = Compare::definition();
    }

}