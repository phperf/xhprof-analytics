<?php
/**
 * @see \Yaoi\Cli\Command\Runner equals
 * @see \Yaoi\Router
 * TODO merge them
 */

namespace Phperf\Xhprof\Ui\Command;

use Phperf\Xhprof\Ui\RequestMapper;
use Phperf\Xhprof\Ui\Response;
use Yaoi\BaseClass;
use Yaoi\Command\Exception;
use Yaoi\Command;
use Yaoi\Io\Request;
use Yaoi\Io\Content\Heading;
use Yaoi\String\Utils;

class Runner extends BaseClass implements \Yaoi\Command\RunnerContract
{
    /** @var Command */
    protected $command;

    /** @var \Yaoi\Command\Option[] */
    protected $optionsArray;

    protected $commandName;
    protected $commandDescription;
    protected $commandVersion;

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

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var RequestReader */
    protected $reader;

    /**
     * @var int Skips specified count of tokens at `argv` head, for embedding in application runner
     * TODO refactor this to basePath (in array form here) from @see \Yaoi\Router
     */
    protected $skipFirstTokens = 0;


    public function run(Request $request = null)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        $this->request = $request;

        try {
            $this->reader = new RequestMapper();
            $this->reader->read($request, $this->command->optionsArray());
        } catch (Exception $exception) {
            $this->response->error($exception->getMessage());
            return $this;
        }


        foreach ($this->reader->values as $name => $value) {
            $this->command->$name = $value;
        }
        $this->command->performAction();
        return $this;
    }


    public function showVersion()
    {
        if ($this->commandName) {

            $versionText = '';
            if ($this->commandVersion) {
                $versionText .= $this->commandVersion . ' ';
            }

            $versionText .= $this->commandName;
            $this->response->addContent(new Heading($versionText));
        }
        if ($this->commandDescription) {
            $this->response->addContent(new Heading($this->commandDescription));
        }
    }

    public static function getPublicName($name)
    {
        return Utils::fromCamelCase($name, '_');
    }
}