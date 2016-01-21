<?php

namespace Phperf\Xhprof\Command\Ui;

use Yaoi\Command;
use Yaoi\Command\Definition;

class Oauth2 extends Command
{
    public $type;

    const TYPE_GITHUB = 'github';

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->type = Command\Option::create()
            ->setEnum(self::TYPE_GITHUB)
            ->setIsUnnamed();
    }

    public function performAction()
    {
        echo 'hooy';
        // TODO: Implement performAction() method.
    }

}