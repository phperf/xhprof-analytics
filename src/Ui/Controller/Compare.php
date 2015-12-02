<?php

namespace Phperf\Xhprof\Ui\Controller;


use Phperf\Xhprof\Ui\Controller\Ancestor;
use Yaoi\Database;
use Yaoi\View\Table\HTML;
use Yaoi\View\Table\JIRA;

class Compare extends Ancestor
{
    public static function compare($run1Name, $run2Name, $inclusive) {
        $res = Database::getInstance()->query("select * from (select coalesce(run1.name, run2.name) as 'function',
                 run1.wall_time/1000000 as run1wt,
                 run2.wall_time/1000000 as run2wt,
                 run2.wall_time/run1.wall_time as wt_diff,
                 run1.count as run1ct,
                 run2.count as run2ct,
                 run2.count/run1.count as ct_diff
               from
                 (select s.name, ss.wall_time, ss.count from
                   phperf_xhprof_symbol_stat as ss
                   left JOIN phperf_xhprof_symbol as s on ss.symbol_id = s.id
                   inner JOIN phperf_xhprof_run as r on ss.run_id = r.id and r.name = :run1
                 where
                   is_inclusive=:inclusive
                  order by wall_time desc limit 50) as run1
                 right join (select s.name, ss.wall_time, ss.count from
                 phperf_xhprof_symbol_stat as ss
                 left JOIN phperf_xhprof_symbol as s on ss.symbol_id = s.id
                 inner JOIN phperf_xhprof_run as r on ss.run_id = r.id and r.name = :run2
               where
                 is_inclusive=:inclusive
                             order by wall_time desc limit 50) as run2 on run1.name = run2.name
               UNION
               select coalesce(run1.name, run2.name),
                 run1.wall_time/1000000 as run1wt,
                 run2.wall_time/1000000 as run2wt,
                 run2.wall_time/run1.wall_time as wt_diff,
                 run1.count as run1ct,
                 run2.count as run2ct,
                 run2.count/run1.count as ct_diff
               from
                 (select s.name, ss.wall_time, ss.count from
                   phperf_xhprof_symbol_stat as ss
                   left JOIN phperf_xhprof_symbol as s on ss.symbol_id = s.id
                   inner JOIN phperf_xhprof_run as r on ss.run_id = r.id and r.name = :run1
                 where
                   is_inclusive=:inclusive
                  order by wall_time desc limit 500) as run1
                 left join (select s.name, ss.wall_time, ss.count from
                 phperf_xhprof_symbol_stat as ss
                 left JOIN phperf_xhprof_symbol as s on ss.symbol_id = s.id
                 inner JOIN phperf_xhprof_run as r on ss.run_id = r.id and r.name = :run2
               where
                 is_inclusive=:inclusive
                            order by wall_time desc limit 500) as run2 on run1.name = run2.name) as unioned
order by coalesce(run1wt,run2wt) desc limit 100;
", array('run1' => $run1Name, 'run2' => $run2Name, 'inclusive' => $inclusive));

        //header("Content-Type: text/plain");
        HTML::create()->setRows($res)->render();

        exit();
    }


}