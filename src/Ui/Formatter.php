<?php

namespace Phperf\Xhprof\Ui;


class Formatter
{
    public static function timeFromNs($time)
    {
        if ($time < 1) {
            return round(1000 * $time, 2) . ' ms';
        } elseif ($time < 60) {
            return round($time, 2) . ' s';
        } else {
            return round($time, 2) . ' s';
        }
    }


    public static function bytes($bytes)
    {
        if ($bytes < 1024) {
            return $bytes;
        }
        elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . 'K';
        }
        else {
            return round($bytes / 1048576, 2) . 'M';
        }
    }

}
