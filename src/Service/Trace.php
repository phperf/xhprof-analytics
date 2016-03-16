<?php

namespace Phperf\Xhprof\Service;

use Phperf\Xhprof\Entity\Stat;

class Trace
{
    public function __construct($symbol, $stat)
    {
        $this->symbol = $symbol;
        $this->stat = $stat;
    }

    /**
     * @var Stat
     */
    public $stat;

    public $symbol;

    /**
     * @var Trace[]
     */
    public $children = array();

}