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
        $runs = Run::statement()->query();
        $rows = array();

        //Compare::options()->run =

        $compare = Compare::createState();

        foreach ($runs as $run) {
            $compare->run = $run->id;
            $run->id = new Anchor($run->id, $this->io->makeAnchor($compare));
            $rows []= $run->toArray(false, true);
        }

        //var_dump($rows);

        $this->response->addContent(new Rows(Processor::create($runs)->map($addLink)));
    }

}