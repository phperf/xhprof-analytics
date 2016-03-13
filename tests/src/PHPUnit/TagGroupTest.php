<?php

namespace src\PHPUnit;

use Phperf\Xhprof\Entity\Project;
use Phperf\Xhprof\Entity\Tag;
use Phperf\Xhprof\Entity\TagGroup;
use Yaoi\Sql\Raw;
use Yaoi\Test\PHPUnit\TestCase;

class TagGroupTest extends TestCase
{
    public function testSetTags() {
        $tagGroup = new TagGroup();
        $tagGroup->setTags(array('tag1', 'tag2', 'tag3'));
        $tagGroup->save();

        $this->assertEquals(array('tag1', 'tag2', 'tag3'), Tag::statement()
            ->where('? IN (?)', Tag::columns()->id, new Raw(TagGroup::findByPrimaryKey($tagGroup->id)->tagIds))
            ->query()
            ->fetchAll(null, Tag::columns()->name));

    }
}