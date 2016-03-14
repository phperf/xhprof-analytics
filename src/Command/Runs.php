<?php

namespace Phperf\Xhprof\Command;


use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Tag;
use Phperf\Xhprof\Entity\TagGroup;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Column;
use Yaoi\Date\TimeMachine;
use Yaoi\Io\Content\Anchor;
use Yaoi\Io\Content\Rows;
use Yaoi\Io\Content\Success;
use Yaoi\Rows\Processor;

class Runs extends Command
{
    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        // TODO: Implement setUpDefinition() method.
    }

    public function performAction()
    {
        /** @var Run[] $runs */
        $runs = Run::statement()->query();

        $compare = Compare::createState();
        $time = TimeMachine::getInstance();

        $tagGroupIds = array();
        foreach ($runs as $run) {
            $tagGroupIds[$run->tagGroupId] = $run->tagGroupId;
        }

        $tagIds = array();
        $tagGroups = array();
        if ($tagGroupIds) {
            $tagGroups = TagGroup::statement()
                ->where('? IN (?)', TagGroup::columns()->id, $tagGroupIds)
                ->query()
                ->fetchAll(TagGroup::columns()->id, TagGroup::columns()->tagIds);

        }
        $tagGroupTags = array();

        foreach ($tagGroups as $tagGroupId => $tagGroupTagIds) {
            if ($tagGroupTagIds) {
                foreach (explode(',', $tagGroupTagIds) as $tagId) {
                    $tagIds[$tagId] = $tagId;
                    $tagGroupTags[$tagGroupId][$tagId] = $tagId;
                }
            }
        }

        if ($tagIds) {
            /** @var Tag[] $tags */
            $tags = Tag::statement()
                ->where('? IN (?)', Tag::columns()->id, $tagIds)
                ->query()
                ->fetchAll(Tag::columns()->id);
        } else {
            $tags = array();
        }


        $this->response->addContent(new Rows(Processor::create($runs)->map(
            function (Run $run) use ($compare, $time, $tags, $tagGroupTags) {
                $compare->runs = $run->id;
                $row = array();
                $row['Run'] = new Anchor($run->id, $this->io->makeAnchor($compare));
                $row['Time'] = $time->date("Y-m-d H:i:s", $run->ut);
                $rowTags = array();
                if (isset($tagGroupTags[$run->tagGroupId])) {
                    foreach ($tagGroupTags[$run->tagGroupId] as $tagId) {
                        $rowTags []= $tags[$tagId]->text;
                    }
                }
                $row['Tags'] = implode(', ', $rowTags);

                $row['Wall Time'] = $run->wallTime / 1000;
                $row['CPU Time'] = $run->cpu;
                $row['Function Calls'] = $run->calls;
                $row['Runs'] = $run->runs;
                return $row;
            }
        )));
    }

}