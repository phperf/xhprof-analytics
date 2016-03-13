<?php

namespace Phperf\Xhprof\Cli;

use Mishak\ArchiveTar\Reader;
use Phperf\Xhprof\Service\ProfileManager;
use Yaoi\Cli\Console;
use Yaoi\Command;
use Yaoi\Cli\Option;
use Yaoi\Command\Definition;
use Yaoi\Database;
use Yaoi\Io\Content\Info;
use Yaoi\Log;
use Yaoi\String\Expression;
use Yaoi\String\StringValue;

class Import extends Command
{
    public $path;
    public $tags;
    public $project;
    public $allowDuplicates;
    public $noSquash;

    /** @var ProfileManager */
    private $profileManager;

    public function performAction()
    {
        if (!file_exists($this->path)) {
            $this->response->error(new Expression('Path ? not found', $this->path));
            return;
        }

        $this->profileManager = new ProfileManager();
        $this->profileManager->noSquash = $this->noSquash;
        $this->profileManager->project = $this->project;
        $this->profileManager->tags = $this->tags;


        if (StringValue::create($this->path)->ends('.tar.gz')) {
            $this->importArchive();
        } else {
            $this->importDirectory();
        }

        if (!$this->noSquash) {
            $this->profileManager->saveStats();
        }

        $this->response->success('All done!');
        $this->response->addContent(new Info('Run ID ' . $this->profileManager->runInstance->id . ' added'));
    }

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->path = Option::create()
            ->setIsUnnamed()
            ->setIsRequired()
            ->setDescription('Path to profiles directory or file');

        $options->project = Option::create()
            ->setType()
            ->setDescription('Project name');

        $options->tags = Option::create()
            ->setIsVariadic()
            ->setDescription('Tags for imported data');

        $options->allowDuplicates = Option::create()
            ->setShortName('d')
            ->setDescription('Allow duplicate profiles (by file path)');

        $options->noSquash = Option::create()
            ->setDescription('Do not combine runs into one');

        $definition->name = 'xhprof-import';
        $definition->description = 'XHPROF data importing tool';
        $definition->version = 'v0.1';
    }


    private function importDirectory()
    {
        $profiles = array();
        if (is_dir($this->path)) {
            if ($handle = opendir($this->path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        $profiles [] = $this->path . '/' . $entry;
                    }
                }
                closedir($handle);
            }
        } else {
            $profiles[] = $this->path;
        }

        $this->count = count($profiles);


        foreach ($profiles as $filename) {
            $this->profileManager->addData($filename, file_get_contents($filename));
        }
    }


    private function importArchive()
    {
        $filename = $this->path;
        $reader = new Reader($filename);
        $reader->setReadContents(false);
        $reader->setBuffer(1000000);
        $count = 0;
        foreach ($reader as $record) {
            if (in_array($record['type'], array(Reader::REGULAR, Reader::AREGULAR), true)) {
                //var_dump($record['filename']);
                ++$count;
            }
        }
        $reader->setReadContents(true);
        $this->count = $count;


        foreach ($reader as $record) {
            if (in_array($record['type'], array(Reader::REGULAR, Reader::AREGULAR), true)) {
                $this->profileManager->addData($this->path . ':' . trim($record['filename']), $record['contents']);
            }
        }
    }

    private function tryAddData($filename, $data)
    {
        try {
            $this->profileManager->addData($filename, $data);
        } catch (Database\Exception $exception) {
            Console::getInstance()->eol();
            print_r($this->profileManager->lastSample);
            Console::getInstance()->printLine($exception->query);
            $this->response->error($exception->getMessage());
        }
    }

}

