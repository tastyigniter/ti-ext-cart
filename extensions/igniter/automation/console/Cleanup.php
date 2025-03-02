<?php

namespace Igniter\Automation\Console;

use Igniter\Automation\Models\AutomationLog;
use Illuminate\Console\Command;

class Cleanup extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'automation:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old records from the automation log.';

    public static $logTTL = 365 / 4;

    public function handle()
    {
        $this->comment('Cleaning old automation log...');
        $logTTL = now()->subDays(config('system.deleteOldRecordsDays', static::$logTTL))->format('Y-m-d H:i:s');

        $amountDeleted = AutomationLog::query()->where('created_at', '<', $logTTL)->delete();

        $this->info("Deleted {$amountDeleted} record(s) from the automation log.");
        $this->comment('All done!');
    }
}
