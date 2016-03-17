<?php

namespace Phperf\Xhprof\Command;

use Yaoi\Twbs\Layout;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Command\Option;
use Yaoi\Database;
use Yaoi\DependencyRepository;
use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Heading;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Request;
use Yaoi\Log;
use Yaoi\Rows\Processor;
use Yaoi\String\Expression;
use Yaoi\Undefined;
use Yaoi\View\Raw;

class Compare extends BaseFilter
{

    /** @var  Layout */
    protected $layout;


    public function __construct()
    {
        $undefined = Undefined::get();
        foreach (static::optionsArray() as $option) {
            $this->{$option->name} = $undefined;
        }
    }

    static function setUpDefinition(Command\Definition $definition, $options)
    {
        parent::setUpDefinition($definition, $options);
        $options->runs = Option::cast($options->runs)
            ->setIsVariadic();
    }


    public function performAction()
    {
        //Console::getInstance()->set(Console::FG_BLUE)->printF('AI AM HIARE')->set(Console::RESET)->eol();

        // TODO do we really need Undefined here
        if ($this->isInclusive instanceof Undefined) {
            $this->isInclusive = 0;
        }

        $expr = \Phperf\Xhprof\Query\Compare::create();
        if (!$this->limit instanceof Undefined) {
            $expr->limit = $this->limit;
        }

        $expr->isInclusive = $this->isInclusive;
        foreach ($this->runs as $runName) {
            $expr->addRun($runName);
        }
        //echo $expr->build(), PHP_EOL;
        if ($this->symbol instanceof Undefined) {
            $res = $expr->build($expr->topSymbolsExpr())->query();
            $rows = array();
            $compare = Compare::createState($this->io);
            foreach ($res as $row) {
                $compare->symbol = $row['function'];
                $row['function'] = new Anchor($row['function'], $this->io->makeAnchor($compare));
                $rows[] = $row;
            }
            $this->response->addContent(new Rows(new \ArrayIterator($rows)));
        }
        else {
            $expr->setSymbol($this->symbol);
            $compare = Compare::createState($this->io);

            $addLink = function ($row) use ($compare)
            {
                $compare->symbol = $row['function'];
                $row['function'] = new Anchor($row['function'], $this->io->makeAnchor($compare));
                return $row;
            };

            //$this->response->addContent(new Heading($this->symbol));
            $res = $expr->build($expr->topSymbolsExpr())->query();
            $this->response->addContent(new Rows($res));

            $this->response->addContent(new Heading('Parents'));
            $res = $expr->build($expr->topParentsExpr())->query();
            $this->response->addContent(new Rows(Processor::create($res)->map($addLink)));

            $this->response->addContent(new Heading('Children'));
            $res = $expr->build($expr->topChildrenExpr())->query();
            $this->response->addContent(new Rows(Processor::create($res)->map($addLink)));
        }
    }
}