<?php

namespace Phperf\Xhprof\Cli;

use Phperf\Xhprof\Entity\Aggregate;
use Phperf\Xhprof\Entity\Project;
use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\AggregateReport;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Phperf\Xhprof\Entity\Tag;
use Phperf\Xhprof\Entity\TagGroup;
use Phperf\Xhprof\Entity\TempRun;
use Phperf\Xhprof\Entity\TempSymbolStat;
use Yaoi\Command;
use Yaoi\Command\Definition;
use Yaoi\Database\Definition\Table;
use Yaoi\Log;

class Migrate extends Command
{
    public $wipe;
    public $dryRun;

    static function setUpDefinition(Definition $definition, $options)
    {
        $options->wipe = Command\Option::create()->setDescription('Recreate tables');
        $options->dryRun = Command\Option::create()->setDescription('Read-only mode');
        $definition->name = 'migrate';
        $definition->description = 'Actualize application data schema';
    }

    public function performAction()
    {
        /** @var Table[] $tables */
        $tables = array(
            Symbol::table(),
            Run::table(),
            RelatedStat::table(),
            SymbolStat::table(),
            Project::table(),
            Tag::table(),
            Aggregate::table(),
            AggregateReport::table(),
            TagGroup::table(),
            TempRun::table(),
            TempSymbolStat::table(),
            TempSymbolStat::table()
        );

        $log = new Log('colored-stdout');
        if ($this->wipe) {
            foreach ($tables as $table) {
                $table->migration()->setLog($log)->setDryRun($this->dryRun)->rollback();
            }
        }
        foreach ($tables as $table) {
            $table->migration()->setLog($log)->setDryRun($this->dryRun)->apply();
        }
    }


}