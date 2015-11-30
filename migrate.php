<?php

namespace Phperf\Xhprof;

use Yaoi\Log;

\xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';

/** @var \Yaoi\Database\Definition\Table[] $tables */
$tables = array(
    Symbol::table(),
    Run::table(),
    RelatedStat::table(),
    SymbolStat::table(),
    Group::table(),
);

$log = new Log('colored-stdout');

foreach ($tables as $table) {
    $table->migration()->setLog($log)->apply();
}

file_put_contents('migration.serialized', serialize(\xhprof_disable()));