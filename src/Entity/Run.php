<?php

namespace Phperf\Xhprof\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;
use Yaoi\Undefined;

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
        $columns->tagIds = Column::create(Column::STRING + Column::NOT_NULL)->setDefault('');
        $columns->projectId = Column::cast(Project::columns()->id)->copy()->setFlag(Column::NOT_NULL, false);

        parent::setUpColumns($columns);
    }

    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->setSchemaName('phperf_xhprof_run');
    }

    public function setTags(array $tagTexts)
    {
        if ($this->projectId instanceof Undefined) {
            throw new Exception('Empty project id');
        }

        if ($this->id instanceof Undefined) {
            throw new Exception('Empty id, please save run before adding tags');
        }

        $tagCols = Tag::columns();

        $tagIdsActual = Tag::statement()
            ->innerJoin('? ON ? = ? AND ? = ?',
                RunTag::table(),
                RunTag::columns()->tagId, $tagCols->id,
                RunTag::columns()->runId, $this->id)
            ->query()
            ->fetchColumns($tagCols->text, $tagCols->id);


        $tagIdsToSkip = array();
        $tagIdsToAdd = array();
        $tagIdsToDelete = array();

        foreach ($tagTexts as $tagText) {
            if (isset($tagIdsActual[$tagText])) {
                $tagIdsToSkip[$tagText] = $tagIdsActual[$tagText];
                unset($tagIdsActual[$tagText]);
            }
            else {
                $tagIdsToAdd[$tagText] = null;
            }
        }

        if (!empty($tagIdsActual)) {
            foreach ($tagIdsActual as $tagText => $tagId) {
                $tagIdsToDelete [$tagText]= $tagId;
            }
        }


        if (!empty($tagIdsToAdd)) {
            $tagIdsExist = Tag::statement()
                ->where('? IN (?)', $tagCols->text, array_keys($tagIdsToAdd))
                ->where('? = ?', $tagCols->projectId, $this->projectId)
                ->query()
                ->fetchColumns($tagCols->text, $tagCols->id);

            foreach ($tagIdsToAdd as $tagText => $tagId) {
                if (!isset($tagIdsExist[$tagText])) {
                    $newTag = new Tag();
                    $newTag->projectId = $this->projectId;
                    $newTag->text = $tagText;
                    $newTag->lastSeen = $this->ut;
                    $newTag->save();
                    $tagIdsToAdd [$tagText] = $newTag->id;
                }
                else {
                    $tagIdsToAdd [$tagText] = $tagIdsExist[$tagText];
                }
            }
        }

        if ($tagIdsToDelete) {
            RunTag::statement()
                ->delete()
                ->where('? = ?', RunTag::columns()->runId, $this->id)
                ->where('? IN (?)', RunTag::columns()->tagId, $tagIdsToDelete)
                ->query()->execute();
        }


        if ($tagIdsToAdd) {
            $rows = array();
            foreach ($tagIdsToAdd as $tagId) {
                $row = new RunTag();
                $row->runId = $this->id;
                $row->tagId = $tagId;
                $rows []= $row;
            }
            RunTag::statement()
                ->insert()
                ->valuesRows($rows)
                ->query()->execute();
        }


        $tagIdsActual = array_merge($tagIdsToSkip, $tagIdsToAdd);
        asort($tagIdsActual);

        $this->tagIds = implode(',', $tagIdsActual);
        return $this;
    }

}