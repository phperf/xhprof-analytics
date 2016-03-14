<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;
use Yaoi\Undefined;

class Run extends Stat
{
    public $id;
    public $projectId;
    public $tagGroupId;
    public $ut;
    public $name;
    public $runs = 1;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->ut = Column::TIMESTAMP + Column::INTEGER;
        $columns->runs = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->name = Column::STRING;
        $columns->projectId = Column::cast(Project::columns()->id)->copy()->setFlag(Column::NOT_NULL, false);
        $columns->tagGroupId = Column::cast(TagGroup::columns()->id)->copy()->setFlag(Column::NOT_NULL, false);

        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
    }


    public function setProjectByName($projectName)
    {
        $project = new Project();
        $project->name = $projectName;
        $project->findOrSave();

        $this->projectId = $project->id;
        $this->save();

        return $this;
    }

    public function setTagsByValues($tags)
    {
        $tagGroup = new TagGroup();
        $tagGroup->setTags($tags);
        $tagGroup->projectId = $this->projectId;
        $tagGroup->findOrSave();

        $this->tagGroupId = $tagGroup->id;
        $this->save();

        return $this;
    }

}