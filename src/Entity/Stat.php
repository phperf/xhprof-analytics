<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

abstract class Stat extends Entity
{
    public $wallTime = 0;
    public $calls = 0;
    public $memoryUsage = 0;
    public $peakMemoryUsage = 0;
    public $cpu = 0;
    public $runs = 0;

    static function setUpColumns($columns)
    {
        $columns->wallTime = Column::create(Column::INTEGER + Column::NOT_NULL);
        $columns->calls = Column::create(Column::INTEGER + Column::NOT_NULL);
        $columns->memoryUsage = Column::create(Column::INTEGER + Column::NOT_NULL);
        $columns->peakMemoryUsage = Column::create(Column::INTEGER + Column::NOT_NULL);
        $columns->cpu = Column::create(Column::INTEGER + Column::NOT_NULL);
        $columns->runs = Column::create(Column::INTEGER + Column::NOT_NULL);

    }

    public function importFromXhData(array $data) {
        $this->calls = $data['ct'];
        $this->wallTime = $data['wt'];
        $this->cpu = isset($data['cpu']) ? $data['cpu'] : 0;
        $this->memoryUsage = isset($data['mu']) ? $data['mu'] : 0;
        $this->peakMemoryUsage = isset($data['pmu']) ? $data['pmu'] : 0;
    }


    public function addXhSample($data, $addTime = true) {
        $this->runs++;

        $this->calls += $data['ct'];
        if ($addTime) {
            $this->wallTime += $data['wt'];
            $this->cpu += isset($data['cpu']) ? $data['cpu'] : 0;
        }
        $this->memoryUsage += isset($data['mu']) ? $data['mu'] : 0;
        $this->peakMemoryUsage = isset($data['pmu']) ? max((int)$this->peakMemoryUsage, $data['pmu']) : 0;
        return $this;
    }


}