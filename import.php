<?php

namespace Phperf\Xhprof;

use Yaoi\Cli\Runner;
use Yaoi\Request;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';
Runner::create(new Cli\Import)->init()->run();
