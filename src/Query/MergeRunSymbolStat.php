<?php

namespace Phperf\Xhprof\Query;

use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Stat;
use Phperf\Xhprof\Entity\SymbolStat;
use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Sql\Raw;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Database\Definition\Column;
use Yaoi\Sql\Symbol;

class MergeRunSymbolStat extends BaseClass
{

    /** @var  Run */
    private $source;
    /** @var  Run[] */
    private $destinations;

    public function __construct(Run $source, $destinations)
    {
        $this->source = $source;
        $this->destinations = $destinations;
    }

    protected function statRowsExpr()
    {
        //SELECT rows.symbol_id,run_ids.run_id, rows.memory_usage FROM (select * from phperf_xhprof_symbol_stat WHERE run_id = 1 LIMIT 10) as rows
        //LEFT JOIN (select 2 AS run_id UNION ALL select 3 UNION ALL select 4) AS run_ids ON 1;
        return Database::getInstance()->select()
            ->from('?', $this->getTable())
            ->where('? = ?', $this->getColumns()->runId, $this->source->id);
    }

    private function unionRunIdsExpr()
    {
        $expr = Database::getInstance()->select();
        $first = true;
        foreach ($this->destinations as $run) {
            if ($first) {
                $expr->select('? AS run_id', $run->id);
                $first = false;
            } else {
                $expr->unionAll('SELECT ?', $run->id);
            }
        }
        return $expr;
    }

    private function joinedExpr()
    {
        $table = $this->getTable('rows');
        /** @var SymbolStat|RelatedStat $columns */
        $columns = $table->columns;
        unset($columns->runId);
        $columnsArray = (array)$columns;

        $expr = Database::getInstance()
            ->select()
            ->select('run_ids.run_id')
            ->from('? AS rows', $this->statRowsExpr());

        foreach ($columnsArray as $column) {
            $expr->select('?', $column);
        }

        $expr->leftJoin('? AS run_ids ON 1', $this->unionRunIdsExpr());
        return $expr;
    }


    /**
     * @param SimpleExpression $columnsExpr
     * @param Stat $columns
     */
    protected function appendStatColumns(SimpleExpression $columnsExpr, $columns)
    {
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->wallTime)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->calls)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->memoryUsage)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->peakMemoryUsage)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->cpu)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->runs)->schemaName));

    }

    /**
     * @param SimpleExpression $expr
     * @param Stat $columns
     */
    protected function appendStatOnDuplicate(SimpleExpression $expr, $columns)
    {
        $this->incrementOnDuplicate($expr, $columns->wallTime);
        $this->incrementOnDuplicate($expr, $columns->calls);
        $this->incrementOnDuplicate($expr, $columns->cpu);
        $this->incrementOnDuplicate($expr, $columns->runs);
        $this->greatestOnDuplicate($expr, $columns->memoryUsage);
        $this->greatestOnDuplicate($expr, $columns->peakMemoryUsage);
    }

    /**
     * @param SimpleExpression $expr
     * @param Column $column
     */
    private function incrementOnDuplicate(SimpleExpression $expr, $column)
    {
        $shortName = new Symbol($column->schemaName);
        $expr->commaExpr('? = VALUES(?) + ?', $shortName, $shortName, $column);
    }


    /**
     * @param SimpleExpression $expr
     * @param Column $column
     */
    private function greatestOnDuplicate(SimpleExpression $expr, $column)
    {
        $shortName = new Symbol($column->schemaName);
        $expr->commaExpr('? = GREATEST(VALUES(?), ?)', $shortName, $shortName, $column);
    }

    /**
     * @param SimpleExpression $columnsExpr
     * @param SymbolStat $columns
     */
    protected function appendKeyColumns(SimpleExpression $columnsExpr, $columns)
    {
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->runId)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->symbolId)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->isInclusive)->schemaName));
    }

    protected function getColumns()
    {
        return SymbolStat::columns();
    }

    protected function getTable($alias = null)
    {
        return SymbolStat::table($alias);
    }

    public function build()
    {
        $columns = $this->getColumns();
        $columnsExpr = Database::getInstance()->expr();
        $this->appendKeyColumns($columnsExpr, $columns);
        $this->appendStatColumns($columnsExpr, $columns);
        $onDuplicate = Database::getInstance()->expr();
        $this->appendStatOnDuplicate($onDuplicate, $columns);

        $expr = Database::getInstance()->expr("INSERT INTO ? \n ? \n (?)", $this->getTable(), $columnsExpr, new Raw($this->joinedExpr()))
            ->appendExpr("\nON DUPLICATE KEY UPDATE ?", new Raw($onDuplicate));


        return $expr;
    }
}