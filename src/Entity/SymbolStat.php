<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class SymbolStat extends Stat
{
    public $symbolId;
    public $runId;
    public $isInclusive;

    static function setUpColumns($columns)
    {
        $columns->symbolId = Symbol::columns()->id;
        $columns->runId = Run::columns()->id;
        $columns->isInclusive = Column::INTEGER;
        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setPrimaryKey($columns->symbolId, $columns->runId, $columns->isInclusive);
    }


    public function subXhSample($data) {
        $this->wallTime -= $data['wt'];
        $this->cpu -= isset($data['cpu']) ? $data['cpu'] : 0;
        $this->memoryUsage -= isset($data['mu']) ? $data['mu'] : 0;
        return $this;
    }

}