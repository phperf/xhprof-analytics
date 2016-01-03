<?php

namespace Phperf\Xhprof\Html;


use Phperf\Xhprof\Html\View\Error;
use Phperf\Xhprof\Html\View\Layout;
use Phperf\Xhprof\Html\View\Message;
use Phperf\Xhprof\Html\View\Success;
use Yaoi\Command;
use Yaoi\Request;
use Yaoi\Undefined;
use Yaoi\View\Semantic\Rows;
use Yaoi\View\Table\HTML;

class Runner implements \Yaoi\Command\Runner
{
    private $layout;
    /** @var Command  */
    private $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
        $this->layout = new Layout();
    }

    public function error($message)
    {
        $this->layout->pushMain(new Error($message));
        return $this;
    }

    public function success($message)
    {
        $this->layout->pushMain(new Success($message));
        return $this;
    }

    public function respond($message)
    {
        if ($message instanceof Rows) {
            $this->layout->pushMain(HTML::create()->setRows($message->getIterator()));
            return $this;
        }
        else {
            $this->layout->pushMain(new Message($message));
            return $this;
        }
    }

    public function init(Request $request = null) {
        if (null === $request) {
            $request = Request::createAuto();
        }

        foreach ($this->command->optionsArray() as $name => $option) {
            if (null !== ($value = $request->request($option->getName()))) {
                $this->command->$name = $value;
            }
        }

    }

    public function run() {
        $this->command->performAction();
    }

}