<?php

namespace Phperf\Xhprof;

use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\Log;


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';

/** @var \Yaoi\Database\Definition\Table[] $tables */
$tables = array(
    Symbol::table(),
    Run::table(),
    RelatedStat::table(),
    SymbolStat::table(),
);

$log = new Log('colored-stdout');

foreach ($tables as $table) {
    $table->migration()->setLog($log)->apply();
}

