<?php


namespace Phperf\Xhprof;

use Phperf\Xhprof\Command\View\Compare;
use Phperf\Xhprof\Html\Controller\SymbolInfo;
use Phperf\Xhprof\Html\Controller\TopExcluded;
use Yaoi\Database;
use Yaoi\View\Table\HTML;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../env/conf.php';

if (isset($_GET['run1']) && isset($_GET['run2'])) {
    Compare::compare($_GET['run1'], $_GET['run2'], $_GET['inclusive']);
}

if (isset($_GET['symbol'])) {
    $controller = new SymbolInfo();
    $controller->symbol = $_GET['symbol'];
    $controller->showSymbol();
}
else{
    TopExcluded::create()->listTop();
}

/*

if (isset($_GET['symbol_id'])) {
    $res = Database::getInstance()->query("select child_symbol_id as symbol_id,sum(wall_time)/1000000 as total_wt, sum(count) as cnt, phperf_xhprof_symbol.name from phperf_xhprof_related_stat
LEFT JOIN phperf_xhprof_symbol on phperf_xhprof_related_stat.child_symbol_id = phperf_xhprof_symbol.id
WHERE parent_symbol_id = $_GET[symbol_id]
GROUP BY child_symbol_id
order by total_wt desc
limit 50;
")->fetchAll();

}
else {
    $res = Database::getInstance()->query("select sum(wall_time) as total_wt, sum(count) as total_ct,count(DISTINCT run_id) as runs, name
from phperf_xhprof_exclusive_stat
LEFT JOIN phperf_xhprof_symbol ON phperf_xhprof_exclusive_stat.symbol_id = phperf_xhprof_symbol.id
group by symbol_id order by total_wt desc
limit 50;
")->fetchAll();

}

$table = new HTML();
foreach ($res as $row) {
    $row['symbol_id'] = '<a href="?symbol_id=' . $row['symbol_id'] . '">' . $row['symbol_id'] . '</a>';
    $table->push($row);
}

$table->render();

*/