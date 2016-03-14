<?php
namespace  Phperf\Xhprof\Command\Ui;

use Phperf\Xhprof\Command\Compare;
use Phperf\Xhprof\Command\CreateProject;
use Phperf\Xhprof\Command\Hog;
use Phperf\Xhprof\Command\Runs;
use Phperf\Xhprof\Service\ProfilingClient;
use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Exception;
use Yaoi\Io\Content\Anchor;
use Yaoi\Undefined;

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
            ->setIsUnnamed()
            ->addToEnum('')
            ->addToEnum(Runs::definition())
            ->addToEnum(Hog::definition())
            ->addToEnum(Compare::definition())
            ->addToEnum(CreateProject::definition());

        $definition->description = 'XHPROF analitics web user interface';
    }

    public function performAction()
    {
        //var_dump($this->action);
        try {
            if ($this->action && !$this->action instanceof Undefined) {
                $url = $this->io->makeAnchor(Index::createState($this->io));
                ProfilingClient::addTag('action', (string)$url);
                $this->action->performAction();
            }
            else {
                $this->response->addContent(new Anchor('Show all runs', $this->io->makeAnchor(Runs::createState())));
            }
        }
        catch (Exception $e) {
            var_dump($e->query);
        }
    }

}