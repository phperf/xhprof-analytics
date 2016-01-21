<?php

namespace Phperf\Xhprof\Ui;


use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Io\Request;

class Runner extends BaseClass implements \Yaoi\Command\RunnerContract
{
    /**
     * @var Command
     */
    protected $command;


    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $io = new Io($this->command);

        try {
            $values = $io->readRequest($request, $this->command->optionsArray());
            foreach ($values as $propertyName => $value) {
                $this->command->$propertyName = $value;
            }
            $this->command->runner = $this;
            $this->command->io = $io;
            $this->command->performAction();
        }
        catch (\Exception $exception) {
            $io->response()->error($exception->getMessage());
        }
    }




}


