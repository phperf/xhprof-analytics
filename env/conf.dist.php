<?php

namespace Phperf\Xhprof;

use Yaoi\Database;
use Yaoi\Log;

Database::register('sqlite:///' . __DIR__ . '/../xh-stat.sqlite');
//Database::register('mysqli://root:@localhost/xhprof_stat2');
//Database::getInstance()->log(new Log('colored-stdout'));
