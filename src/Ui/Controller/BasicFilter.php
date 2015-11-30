<?php

namespace Phperf\Xhprof\Ui\Controller;

use Phperf\Xhprof\Ui\View\Layout;
use Yaoi\BaseClass;
use Yaoi\String\Expression;
use Yaoi\View\Raw;

class BasicFilter extends BaseClass
{
    public $runId;
    public $symbol;
    public $limit;
    public $offset = 0;

    /** @var  Layout */
    protected $layout;

    public function __construct()
    {
        $this->layout = new Layout();
    }

    public function error() {
        $this->layout->setMain(
            Raw::create(
                Expression::create(func_get_args())
            )
        )->render();
    }



}