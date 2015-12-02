<?php

namespace Phperf\Xhprof\Ui\Controller;

use Phperf\Xhprof\Ui\View\Layout;
use Yaoi\DependencyRepository;
use Yaoi\Request;
use Yaoi\String\Expression;
use Yaoi\Undefined;
use Yaoi\View\Raw;

abstract class Ancestor extends \Yaoi\Command
{
    public $run;
    public $runTwo;
    public $symbol;

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
        $this->layout->setMain(
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

    public function getUrl(array $params)
    {
        $request = $this->request();
        foreach (static::optionsArray() as $name => $option) {
            $option->getName();
            if ($request->request($option->getName()))
        }
        return http_build_query();
    }


    public function __destruct()
    {
        DependencyRepository::delete($this->requestRef);
    }


}