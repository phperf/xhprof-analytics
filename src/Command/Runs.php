<?php

namespace Phperf\Xhprof\Command;


use Phperf\Xhprof\Entity\Run;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Column;
use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\Success;

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
        $idColumn = Column::cast(Run::columns()->id)->schemaName;
        $rows = array();

        foreach ($runs as $run) {
            $run[$idColumn] = new Anchor($run[$idColumn], '/about');
            $rows []= $run;
        }

        $this->response->addContent(new Rows(new \ArrayIterator($rows)));
    }

}