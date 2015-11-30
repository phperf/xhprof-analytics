<?php

namespace Phperf\Xhprof;

use Yaoi\BaseClass;
use Yaoi\Database;

class Export extends BaseClass
{
    public function exportCombined() {
        $res = Database::getInstance()->query("select
  child.name as child,
  parent.name as parent,
  sum(wall_time) as wt,
  sum(count) as ct,
  sum(cpu) as cpu,
  round(avg(memory_usage)) as mu,
  max(peak_memory_usage) as pmu
from phperf_xhprof_related_stat
  LEFT JOIN phperf_xhprof_symbol as child ON phperf_xhprof_related_stat.child_symbol_id = child.id
  LEFT JOIN phperf_xhprof_symbol as parent ON phperf_xhprof_related_stat.parent_symbol_id = parent.id
GROUP BY child_symbol_id, parent_symbol_id;")->fetchAll();

        $result = array();
        foreach ($res as $row) {
            $result [$row['parent'] . '==>' . $row['child']] = array(
                'ct' => (int)$row['ct'],
                'wt' => (int)$row['wt'],
                'cpu' => (int)$row['cpu'],
                'mu' => (int)$row['mu'],
                'pmu' => (int)$row['pmu']
            );
        }


        $res = Database::getInstance()->query("select
  symbol.name as symbol,
  sum(wall_time) as wt,
  sum(count) as ct,
  sum(cpu) as cpu,
  round(avg(memory_usage)) as mu,
  max(peak_memory_usage) as pmu
from phperf_xhprof_single_stat
  LEFT JOIN phperf_xhprof_symbol as symbol ON phperf_xhprof_single_stat.symbol_id = symbol.id
GROUP BY symbol_id;")->fetchAll();


        foreach ($res as $row) {
            $result[$row['symbol']] = array(
                'ct' => (int)$row['ct'],
                'wt' => (int)$row['wt'],
                'cpu' => (int)$row['cpu'],
                'mu' => (int)$row['mu'],
                'pmu' => (int)$row['pmu']
            );

        }

        file_put_contents('export.serialized', serialize($result));
    }

}