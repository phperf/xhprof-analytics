<?php
namespace Phperf\Xhprof\Ui\Application;

use Yaoi\Command\Io\Io;
use Yaoi\Twbs\Response;
use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Io\Request;

class Runner extends BaseClass implements \Yaoi\Command\RunnerContract
{
    /** @var Command */
    protected $command;

    /** @var \Yaoi\Command\Option[] */
    protected $optionsArray;

    protected $commandName;
    protected $commandDescription;
    protected $commandVersion;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var Io */
    protected $reader;


    public function __construct(Command $command)
    {
        $this->command = $command;
        $definition = $command->definition();
        $this->commandName = $definition->name;
        $this->commandVersion = $definition->version;
        $this->commandDescription = $definition->description;
        $this->optionsArray = $this->command->optionsArray();
        $this->response = new Response();
        $command->setResponse($this->response);
    }

    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $this->request = $request;

        try {
            if (!$this->command instanceof Command\Application) {
                throw new Command\Exception('Application required', Command\Exception::INVALID_ARGUMENT);
            }

            $this->reader = new Io();
            $this->reader->readOptions($request, $this->command->optionsArray());
        } catch (Command\Exception $exception) {
            if (empty($this->reader->values['action'])) { // TODO symbolize 'action' literal
                $this->response->error($exception->getMessage());
                $this->response->addContent('Use --help to show information.');
                return $this;
            }
        }

        foreach ($this->reader->values as $name => $value) {
            $this->command->$name = $value;
        }


        if (isset($this->command->action)) {
            $action = $this->command->action;
            $commandDefinition = $this->command->definition()->actions[$action];
            $command = new $commandDefinition->commandClass;

            $runner = new \Yaoi\Cli\Command\Runner($command);
            $runner->commandName = $this->commandName . ' ' . $action;
            $runner->commandVersion = $this->commandVersion;
            $runner->commandDescription = $this->commandDescription
                . ($runner->commandDescription ? PHP_EOL . $runner->commandDescription : '');
            $runner->skipFirstTokens = 1;
            $runner->run($request);
        }

        return $this;
    }

}