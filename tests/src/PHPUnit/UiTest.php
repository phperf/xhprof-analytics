<?php

namespace Phperf\Xhprof\Entity;

use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\Hog;
use Phperf\Xhprof\Command\Runs;
use Phperf\Xhprof\Command\Ui\Index;
use Yaoi\Command\Io;
use Yaoi\Command\Web\RequestMapper;
use Yaoi\Io\Request;
use Yaoi\Twbs\Response;
use Yaoi\Twbs\Runner;

class UiTest extends \Yaoi\Test\PHPUnit\TestCase
{

    public function setUp()
    {
        $request = new Request();
        $requestMapper = new RequestMapper($request);
        $response = new Response();
        $this->mirrorIo = new Io(Index::definition(), $requestMapper, $response);
    }

    /** @var  Io */
    private $mirrorIo;

    private function makeRequest($commandState)
    {
        $request = new Request();
        $request->server()->REQUEST_URI = $url = (string)$this->mirrorIo->makeAnchor($commandState);
        $url = explode('?', $url);
        if (isset($url[1])) {
            parse_str($url[1], $url);
            foreach ($url as $key => $value) {
                $request->setParam(Request::REQUEST, $key, $value);
            }
        }
        return $request;
    }


    public function testIndexPage()
    {
        ob_start();
        Runner::run(Index::definition());
        ob_end_clean();
    }


    public function testRunsPage()
    {
        $runs = Runs::createState();
        $request = $this->makeRequest($runs);

        ob_start();
        Runner::run(Index::definition(), $request);
        ob_end_clean();

    }



    public function testHogsPage()
    {
        /** @var Run $run */
        $run = Run::statement()->limit(1)->query()->fetchRow();
        //var_dump($run);
        $hogState = Hog::createState();
        $hogState->runId = $run->id;
        ob_start();
        Runner::run(Index::definition(), $this->makeRequest($hogState));
        ob_end_clean();

    }


    public function testCompare()
    {
        /** @var Run $run */
        $run = Run::statement()->limit(1)->query()->fetchRow();

        $compareState = Compare::createState();
        $compareState->runs = $run->id;

        ob_start();
        Runner::run(Index::definition(), $this->makeRequest($compareState));
        ob_end_clean();
    }

    public function testCompareSymbol()
    {
        /** @var Run $run */
        $run = Run::statement()->limit(1)->query()->fetchRow();

        /** @var SymbolStat $stat */
        $stat = SymbolStat::statement()
            ->where('? = ?', SymbolStat::columns()->runId, $run->id)
            ->limit(1)
            ->query()
            ->fetchRow();

        $symbol = Symbol::findByPrimaryKey($stat->symbolId);

        $compareState = Compare::createState();
        $compareState->runs = $run->id;
        $compareState->symbol = $symbol->name;

        ob_start();
        Runner::run(Index::definition(), $this->makeRequest($compareState));
        ob_end_clean();
    }

}