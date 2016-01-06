<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database;
use Yaoi\Sql\Raw;

class RunTest extends \Yaoi\Test\PHPUnit\TestCase
{
    public function testSetTags()
    {
        $project = new Project();
        $project->name = 'test-project';
        $project->save();

        $run = new Run();
        $run->projectId = $project->id;
        $run->ut = time();
        $run->tagIds = '';
        $run->save();

        $run->setTags(array('tag1', 'tag2', 'tag3'));
        $run->save();

        $this->assertEquals(array('tag1', 'tag2', 'tag3'), Tag::statement()
            ->where('? IN (?)', Tag::columns()->id, new Raw(Run::findByPrimaryKey($run->id)->tagIds))
            ->query()
            ->fetchAll(null, Tag::columns()->text));
    }

}