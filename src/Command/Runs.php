<?php

namespace Phperf\Xhprof\Command;


use Phperf\Xhprof\Entity\Run;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Column;
use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\Success;
use Yaoi\Rows\Processor;

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
        /** @var Run[] $runs */
        $runs = Run::statement()->bindResultClass()->query();

        $compare = Compare::createState();
        $addLink = function($row) use ($compare) {
            $compare->runs = array($row['id']);
            $row['id'] = new Anchor($row['id'], $this->io->makeAnchor($compare));
            return $row;
        };

        $this->response->addContent(new Rows(Processor::create($runs)->map($addLink)));
    }

}