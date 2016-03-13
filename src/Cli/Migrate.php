<?php

namespace Phperf\Xhprof\Cli;

use GeoTool\Entities\Points;
use GeoTool\Entities\Segment10;
use GeoTool\Entities\Segment100;
use GeoTool\Entities\Segment10k;
use GeoTool\Entities\Segment1k;
use GeoTool\Entities\Segment5;
use GeoTool\Entities\Segment50;
use GeoTool\Entities\Segment500;
use GeoTool\Entities\Segment5k;
use Phperf\Xhprof\Entity\Aggregate;
use Phperf\Xhprof\Entity\AggregateDay;
use Phperf\Xhprof\Entity\AggregateHour;
use Phperf\Xhprof\Entity\AggregateMinute;
use Phperf\Xhprof\Entity\AggregateMonth;
use Phperf\Xhprof\Entity\AggregateWeek;
use Phperf\Xhprof\Entity\Project;
use Phperf\Xhprof\Entity\RelatedStat;
use Phperf\Xhprof\Entity\AggregateReport;
use Phperf\Xhprof\Entity\Run;
use Phperf\Xhprof\Entity\Symbol;
use Phperf\Xhprof\Entity\SymbolStat;
use Phperf\Xhprof\Entity\Tag;
use Phperf\Xhprof\Entity\TagGroup;
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