<?php

namespace Phperf\Xhprof\Ui;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\Io\Response;
use Yaoi\String\Utils;
use Yaoi\Undefined;

class Io extends BaseClass
{
    /**
     * @var Io
     */
    public $parent;

    public $commandState;

    /** @var Request */
    protected $request;
    /** @var  Command\Definition */
    protected $definition;

    /** @var  Option[] */
    protected $flatOptions = array();

    public function __construct(Request $request, Command\Definition $definition, Io $parent = null)
    {
        $this->request = $request;
        $this->parent = $parent;
        $this->definition = $definition;
        $this->flatDefinition = new Command\Definition();

        $this->commandState = $this->readRequest($request, $definition->optionsArray());
    }


    /**
     * @param $request
     * @param Option[] $options
     */
    protected function flattenOptions($request, $options) {
        foreach ($options as $option) {

        }
    }


    /**
     * @param Request $request
     * @param Option[] $options
     * @return \stdClass
     * @throws Command\Exception
     */
    protected function readRequest(Request $request, array $options)
    {
        $commandState = new \stdClass();

        foreach ($options as $option) {
            $publicName = $this->getPublicName($option->name);
            if (false !== ($value = $request->request($publicName, false)
                )
            ) {

                if (Option::TYPE_ENUM === $option->type) {
                    if (!isset($option->values[$value])) {
                        throw new Command\Exception('Invalid value for ' . $publicName, Command\Exception::INVALID_VALUE);
                    }



                }




                if (!$value && Option::TYPE_VALUE === $option->type) {
                    throw new Command\Exception('Value required for ' . $publicName, Command\Exception::VALUE_REQUIRED);
                }

                if ($option->isVariadic) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                }

                if (Option::TYPE_BOOL === $option->type) {
                    $value = (bool)$value;
                }

                $commandState->{$option->name} = $value;
            }
            else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option '. $publicName .' required', Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $commandState;
    }


    public static function getPublicName($name)
    {
        return Utils::fromCamelCase($name, '_');
    }

    public function makeUri(Command $command)
    {
        $url = $this->basePath;

        if (isset($this->parent)) {
        }

        $values = array();
        foreach ($command->optionsArray() as $name => $option) {
            if (!$command->$name instanceof Undefined) {
                $values[$name] = $command->$name;
            }
        }
        $url .= '?' . http_build_query($values);
        return $url;
    }


    public function child()
    {
        $io = new static;
        $io->parent = $this;
        $io->request = $this->request;
        $io->response = $this->response;
        return $io;
    }


    public function buildUri($commandState)
    {
        $this->buildUriComponents();
    }

    private function buildUriComponents()
    {
        if ($this->parent) {

        }
    }


    public function setUpCommand(Command $command) {

    }



}