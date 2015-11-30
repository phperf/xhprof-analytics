<?php

namespace Phperf\Xhprof;

use Phperf\Xhprof\Cli\Import;
use Yaoi\Request;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';
Cli\Import::create()->init()->run();
