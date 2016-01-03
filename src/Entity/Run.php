<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class Run extends Stat
{
    public $id;
    public $projectId;
    public $ut;
    public $name;
    public $runs;
    public $tagIds;

    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->ut = Column::TIMESTAMP + Column::INTEGER;
        $columns->runs = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault(0);
        $columns->name = Column::STRING;
        $columns->tagIds = Column::STRING + Column::NOT_NULL;

        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('phperf_xhprof_run');
    }

    public function setTags(array $tagTexts)
    {
        if (!$this->projectId) {
            throw new Exception('Empty project id');
        }

        $tagCols = Tag::columns();
        $result = Tag::statement()
            ->where('? IN (?)', Tag::columns()->text, $tagTexts)
            ->where('? = ?', Tag::columns()->projectId, $this->projectId)
            ->query()
            ->fetchAll($tagCols->text);

        foreach ($tagTexts as $tagText) {
            if (!isset($result[$tagText])) {
                $newTag = new Tag();
                $newTag->projectId = $this->projectId;
                $newTag->text = $tagText;
                $newTag->lastSeen = $this->ut;
                $newTag->save();
                $result[$tagText] = $newTag;
            }
        }

        // TODO continue
    }

}