<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\BaseClass;
use Yaoi\Log;

class Migrations extends BaseClass
{
    public function run(Log $log = null) {
        /** @var \Yaoi\Database\Definition\Table[] $tables */
        $tables = array(
            Symbol::table(),
            Run::table(),
            RelatedStat::table(),
            SymbolStat::table(),
            Project::table(),
            RunTag::table(),
            Tag::table(),
        );

        if (null === $log) {
            $log = Log::nil();
        }

        foreach ($tables as $table) {
            $table->migration()->setLog($log)->apply();
        }
    }
}