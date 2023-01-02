<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeletDuplicatedRowsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:deleteDuplicated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      // find the duplicate ids first.
      $duplicateIds = \DB::table("matches")
        ->selectRaw("min(id) as id")
        ->groupBy("event_id")
        ->havingRaw('count(id) > ?', [1])
        ->pluck("id");

      // Now delete and exclude those min ids.
      \DB::table("matches")
        ->whereNotIn("id", $duplicateIds)
        ->havingRaw('count(id) > ?', [1])
        ->delete();
    }
}
