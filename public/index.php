<?php

namespace Phperf\Xhprof;

use Phperf\Xhprof\Command\Ui\Index;
use Phperf\Xhprof\Service\ProfilingClient;
use Phperf\Xhprof\Ui\Runner;

xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env/conf.php';

ProfilingClient::stopOnShutdown();
ProfilingClient::setProject('xhprof-analytics');
ProfilingClient::addTag('server', gethostname());


Runner::run(Index::definition());



