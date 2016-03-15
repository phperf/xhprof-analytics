<?php

namespace Phperf\Xhprof\Ui;


class Formatter
{
    public static function timeFromNs($time)
    {
        if ($time < 1) {
            return round(1000 * $time, 2) . ' ms';
        }
        elseif ($time < 60) {
            return round($time, 2) . ' s';
        }
        else {
            return round($time, 2) . ' s';
        }
    }

}
