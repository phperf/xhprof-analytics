<?php

namespace Phperf\Xhprof\Ui;

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

        $response = new Response();

        try {
            $io = new Command\Io($definition, $request, $response);
            $io->command->performAction();
        }
        catch (\Exception $exception) {
            $response->error($exception->getMessage());
        }
    }




}


