<?php
namespace Phperf\Xhprof;

use Yaoi\Database;
use Yaoi\Log;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env/conf.php';

/*
$dbFilePath = sys_get_temp_dir() . '/xh-tests.sqlite';
register_shutdown_function(function()use($dbFilePath) {
    unlink($dbFilePath);
});
Database::register('sqlite:///' . $dbFilePath);
unset($dbFilePath);

Entity\Migrations::create()->run(
//    new Log('colored-stdout')
);

Database::getInstance()->log(new Log('colored-stdout'));
*/