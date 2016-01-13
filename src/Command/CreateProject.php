<?php

namespace Phperf\Xhprof\Command;


use Phperf\Xhprof\Entity\Project;
use Yaoi\Command;
use Yaoi\Command\Definition;

class CreateProject extends Command
{
    public $name;
    public $apiKey;
    public $description;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->name = Command\Option::create()
            ->setIsUnnamed()
            ->setType()
            ->setIsRequired()
            ->setDescription('Project name');

        $options->apiKey = Command\Option::create()
            ->setType()
            ->setDescription('Set API key');

        $options->description = Command\Option::create()
            ->setType()
            ->setDescription('Set project description');
    }

    public function performAction()
    {
        $project = new Project();
        $project->name = $this->name;
        $project->apiKey = $this->apiKey;
        $project->description = $this->description;
        $project->save();

        $this->response->success('Project ID ' . $project->id . ' created');
    }


}