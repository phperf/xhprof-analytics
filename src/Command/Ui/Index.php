<?php
namespace  Phperf\Xhprof\Command\Ui;

use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\CreateProject;
use Phperf\Xhprof\Command\Runs;
use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Exception;

class Index extends Command
{
    /**
     * @var Command
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
            ->setIsRequired()
            ->addToEnum(Runs::definition())
            ->addToEnum(Oauth2::definition())
            ->addToEnum(Compare::definition())
            ->addToEnum(CreateProject::definition());

        $definition->description = 'XHPROF analitics web user interface';
    }

    public function performAction()
    {
        var_dump($this->action);
        try {
            $this->action->performAction();
        }
        catch (Exception $e) {
            var_dump($e->query);
        }
    }

}