<?php

namespace Phperf\Xhprof;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/env/conf.php';

Export::create()->exportCombined();