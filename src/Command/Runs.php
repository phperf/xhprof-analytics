<?php

namespace Phperf\Xhprof\Command;


use Phperf\Xhprof\Entity\Run;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Io\Content\Rows;

class Runs extends Command
{
    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        // TODO: Implement setUpDefinition() method.
    }

    public function performAction()
    {
        $runs = Run::statement()->bindResultClass()->query();
        $this->response->addContent(new Rows($runs));
    }

}