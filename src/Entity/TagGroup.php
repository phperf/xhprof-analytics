<?php

namespace Phperf\Xhprof\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class TagGroup extends Entity
{
    public $id;
    public $tagIds;
    public $projectId;
    public $lastSeen;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->tagIds = Column::create(Column::STRING + Column::NOT_NULL)
            ->setDefault('')
            ->setUnique();
        $columns->projectId = Column::cast(Project::columns()->id)->copy()->setFlag(Column::NOT_NULL, false);
        $columns->lastSeen = Column::INTEGER + Column::TIMESTAMP;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        // no op
    }


    public function setTags(array $tags)
    {
        $tagCols = Tag::columns();


        $tagTexts = array();
        $nameValues = array();
        foreach ($tags as $name => $value) {
            $tagText = $value ? $name . ':' . $value : $name;
            $tagTexts []= $tagText;
            $nameValues[$tagText] = array($name, $value);
        }

        $tagIdsToAdd = array();

        $tagIdsByText = Tag::statement()
            ->where('? IN (?)', $tagCols->text, $tagTexts)
            ->query()
            ->fetchAll($tagCols->text, $tagCols->id);

        foreach ($tagTexts as $tagText) {
            if (!isset($tagIdsByText[$tagText])) {
                $tagIdsToAdd[$tagText] = null;
            }
        }

        foreach ($tagIdsToAdd as $tagText => $tagId) {
            $newTag = new Tag();
            $newTag->text = $tagText;
            list($newTag->name, $newTag->value) = $nameValues[$tagText];
            $newTag->save();
            $tagIdsByText [$tagText] = $newTag->id;
        }

        asort($tagIdsByText);

        /**
         * @todo consider helper crc32 index here
         */
        $this->tagIds = implode(',', $tagIdsByText);
        return $this;
    }


}