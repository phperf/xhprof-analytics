<?php

namespace Phperf\Xhprof\Ui;

use Phperf\Xhprof\Html\View\Layout;
use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Io\Request;

class Runner extends BaseClass //implements \Yaoi\Command\RunnerContract
{
    public static function run(Command\Definition $definition, Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $requestMapper = new Command\Web\RequestMapperWithPath($request);
        $response = new Response();

        $layout = new Layout();
        $layout->pushMain($response);


        try {
            $io = new Command\Io($definition, $requestMapper, $response);
            $io->getCommand()->performAction();
        }
        catch (\Exception $exception) {
            var_dump($exception);
            $response->error($exception->getMessage());
        }

        $layout->render();
    }




}


