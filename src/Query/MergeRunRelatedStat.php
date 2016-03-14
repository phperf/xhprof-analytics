<?php

namespace Phperf\Xhprof\Query;


use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\TempRelatedStat;
use Yaoi\Database\Definition\Column;
use Yaoi\Sql\SimpleExpression;
use Yaoi\Sql\Symbol;

class MergeRunRelatedStat extends MergeRunSymbolStat
{
    protected function getTable($alias = null)
    {
        return RelatedStat::table($alias);
    }

    protected function getColumns()
    {
        return RelatedStat::columns();
    }

    protected function getTempColumns()
    {
        return TempRelatedStat::columns();

    }

    protected function getTempTable()
    {
        return TempRelatedStat::table();
    }


    /**
     * @param SimpleExpression $columnsExpr
     * @param RelatedStat $columns
     */
    protected function appendKeyColumns(SimpleExpression $columnsExpr, $columns)
    {
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->runId)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->parentSymbolId)->schemaName));
        $columnsExpr->commaExpr('?', new Symbol(Column::cast($columns->childSymbolId)->schemaName));
    }
}