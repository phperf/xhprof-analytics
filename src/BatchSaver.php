<?php

namespace Phperf\Xhprof;


use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;
use Yaoi\Database\Exception;

class BatchSaver
{
    /** @var Entity[]  */
    private $items = array();
    private $count = 0;
    public $pageSize = 500;

    /** @var  Table */
    private $table;
    private $entityClass;


    public function init(Entity $item) {
        $this->entityClass = get_class($item);
        $this->table = $item->table();
    }

    public function add(Entity $item) {
        if (null === $this->entityClass) {
            $this->init($item);
        }
        else {
            if (!$item instanceof $this->entityClass) {
                throw new Exception('Incompatible item class ' . get_class($item) . ', required ' . $this->entityClass);
            }
        }

        $this->items[] = $item;
        ++$this->count;

        if ($this->count >= $this->pageSize) {
            $this->flush();
        }
        return $this;
    }

    public function flush() {
        //echo "FLUSHING", PHP_EOL;


        if (!$this->count) {
            return $this;
        }

        $database = $this->table->database();
        $insert = $database->insert($this->table->schemaName);
        foreach ($this->items as $item) {
            $insert->valuesRow($item->toArray());
        }
        $insert->query();


        $this->items = array();
        $this->count = 0;
        return $this;
    }

}