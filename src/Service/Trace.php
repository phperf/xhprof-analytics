<?php

namespace Phperf\Xhprof\Service;

use Phperf\Xhprof\Entity\Stat;

class Trace
{
    public function __construct($symbol, $wallTime)
    {
        $this->symbol = $symbol;
        $this->wallTime = $wallTime;
    }

    /**
     * @var Stat
     */
    public $wallTime;

    public $symbol;

    /**
     * @var Trace[]
     */
    public $children = array();

}