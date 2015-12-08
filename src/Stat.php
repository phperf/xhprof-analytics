<?php

namespace Phperf\Xhprof;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

abstract class Stat extends Entity
{
    public $wallTime = 0;
    public $count = 0;
    public $memoryUsage = 0;
    public $peakMemoryUsage = 0;
    public $cpu = 0;

    static function setUpColumns($columns)
    {
        $columns->wallTime = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->count = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->memoryUsage = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->peakMemoryUsage = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->cpu = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
    }

    public function importFromXhData(array $data) {
        $this->count = $data['ct'];
        $this->wallTime = $data['wt'];
        $this->cpu = isset($data['cpu']) ? $data['cpu'] : 0;
        $this->memoryUsage = isset($data['mu']) ? $data['mu'] : 0;
        $this->peakMemoryUsage = isset($data['pmu']) ? $data['pmu'] : 0;
    }


    public function addXhSample($data, $addTime = true) {
        $this->count += $data['ct'];
        if ($addTime) {
            $this->wallTime += $data['wt'];
            $this->cpu += isset($data['cpu']) ? $data['cpu'] : 0;
        }
        $this->memoryUsage += isset($data['mu']) ? $data['mu'] : 0;
        $this->peakMemoryUsage = isset($data['pmu']) ? max((int)$this->peakMemoryUsage, $data['pmu']) : 0;
        return $this;
    }


}