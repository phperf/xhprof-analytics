<?php

namespace Phperf\Xhprof\Command;

use Phperf\Xhprof\Html\View\Layout;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Command\Option;
use Yaoi\Database;
use Yaoi\DependencyRepository;
use Yaoi\Io\Content\Heading;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Request;
use Yaoi\Log;
use Yaoi\String\Expression;
use Yaoi\Undefined;
use Yaoi\View\Raw;

class Compare extends BaseFilter
{

    /** @var  Layout */
    protected $layout;

    public function __construct(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }
        $this->requestRef = DependencyRepository::add($request);

        $undefined = Undefined::get();
        foreach (static::optionsArray() as $option) {
            $this->{$option->name} = $undefined;
        }


        $this->layout = new Layout();
    }

    public function error() {
        $this->layout->pushMain(
            Raw::create(
                Expression::create(func_get_args())
            )
        )->render();
    }

    protected $requestRef;

    /**
     * @return Request
     */
    public function request() {
        return DependencyRepository::get($this->requestRef);
    }

    /**
     * @return $this|\stdClass
     */
    public function getParams() {
        static $keys = null;
        if (null === $keys) {
            $keys = array_keys(static::optionsArray());
        }

        $object = new \stdClass();
        foreach ($keys as $name) {
            if (!$this->$name instanceof Undefined) {
                $object->$name = $this->$name;
            }
        }

        return $object;
    }

    public function getUrl(\stdClass $params) {
        static $propertiesToParams;
        if (null === $propertiesToParams) {
            $propertiesToParams = array();
            foreach (static::optionsArray() as $propertyName => $option) {
                $propertiesToParams[$propertyName] = $option->getPublicName();
            }
        }
        $params = (array)$params;
        $values = array();
        foreach ($params as $propertyName => $value) {
            $values [$propertiesToParams[$propertyName]] = $value;
        }
        return http_build_query($values);
    }

    public function __destruct()
    {
        DependencyRepository::delete($this->requestRef);
    }

    static function setUpDefinition(Command\Definition $definition, $options)
    {
        parent::setUpDefinition($definition, $options);
        $options->run = Option::cast($options->run)
            ->setIsVariadic();
    }


    public function performAction()
    {
        //Console::getInstance()->set(Console::FG_BLUE)->printF('AI AM HIARE')->set(Console::RESET)->eol();

        if ($this->isInclusive instanceof Undefined) {
            $this->isInclusive = 0;
        }

        $expr = \Phperf\Xhprof\Query\Compare::create();
        if (!$this->limit instanceof Undefined) {
            $expr->limit = $this->limit;
        }

        $expr->isInclusive = $this->isInclusive;
        foreach ($this->run as $runName) {
            $expr->addRun($runName);
        }
        //echo $expr->build(), PHP_EOL;
        if ($this->symbol instanceof Undefined) {
            $res = $expr->build($expr->topSymbolsExpr())->query();
            $this->response->addContent(new Rows($res));
        }
        else {
            $expr->setSymbol($this->symbol);

            $this->response->addContent(new Heading($this->symbol));
            $res = $expr->build($expr->topSymbolsExpr())->query();
            $this->response->addContent(new Rows($res));

            $this->response->addContent(new Heading('Parents'));
            $res = $expr->build($expr->topParentsExpr())->query();
            $this->response->addContent(new Rows($res));

            $this->response->addContent(new Heading('Children'));
            $res = $expr->build($expr->topChildrenExpr())->query();
            $this->response->addContent(new Rows($res));
        }
    }
}