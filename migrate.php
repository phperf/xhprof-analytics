<?php

namespace Phperf\Xhprof;

use Phperf\Xhprof\Entity\Migrations;
use Yaoi\Log;


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';

$log = new Log('colored-stdout');
Migrations::create()->run($log);
