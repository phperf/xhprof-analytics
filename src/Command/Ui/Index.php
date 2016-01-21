<?php
namespace  Phperf\Xhprof\Command\Ui;

use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\CreateProject;
use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;

class Index extends Command
{
    /**
     * @var Definition
     */
    public $action;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->action = Option::create()
            ->setDescription('Root action')
            ->addToEnum(Oauth2::definition())
            ->addToEnum(Compare::definition())
            ->addToEnum(CreateProject::definition());

        $definition->description = 'XHPROF analitics web user interface';
    }

    public function performAction()
    {
        $commandClass = $this->action->commandClass;
        $actionCommand = new $commandClass;

        $this->runner->run();

        // TODO: Implement performAction() method.
    }

}