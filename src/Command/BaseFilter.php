<?php

namespace Phperf\Xhprof\Command;


use Yaoi\Cli\Option;
use Yaoi\Command;

abstract class BaseFilter extends Command
{
    public $run;
    public $symbol;
    public $isInclusive = 0;
    public $limit = 50;
    public $minWtPercent;

    static function setUpDefinition(Command\Definition $definition, $options)
    {
        $options->run = Command\Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->setDescription('Run name')
            ->setType();

        $options->symbol = Command\Option::create()->setDescription('Function name')->setType();
        $options->isInclusive = Command\Option::create()->setDescription('Show inclusive stats');
        $options->limit = Option::create()->setType()->setDescription('Limit number of rows');
    }

}