<?php

namespace Phperf\Xhprof\Entity;


class TempRun extends Run
{
    public function delete()
    {
        TempSymbolStat::statement()->delete()->where('? = ?', TempSymbolStat::columns()->runId, $this->id)->query();
        TempRelatedStat::statement()->delete()->where('? = ?', TempRelatedStat::columns()->runId, $this->id)->query();
        return parent::delete();
    }

}