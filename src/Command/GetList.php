<?php

namespace Phperf\Xhprof\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

class GetList extends Command
{
    const SYMBOL = 'symbol';

    public $type;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->type = Command\Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->setEnum(self::SYMBOL);

        // TODO: Implement setUpDefinition() method.
    }

    public function performAction()
    {
        // TODO: Implement performAction() method.
    }

}