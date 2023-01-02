<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\LoadDayEnvent;

class LoadDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:loadDay';
    private $arr;
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
        $this->arr = $arr = array();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */

    public function diasFutebol() 
    {
        
        for($i=0; $i < 3; $i++) {
            $data = date('D', strtotime('+'.$i.'day'));

            $semana = array(
                'Sun' => 'Domingo', 
                'Mon' => 'Segunda-Feira',
                'Tue' => 'Terca-Feira',
                'Wed' => 'Quarta-Feira',
                'Thu' => 'Quinta-Feira',
                'Fri' => 'Sexta-Feira',
                'Sat' => 'Sábado'
            );

            $this->arr[$i]['day'] = $semana["$data"];
            $this->arr[$i]['num'] = $i;
        }

        return $this->arr;
   
    }

    public function handle()
    {
        broadcast(new LoadDayEnvent($this->diasFutebol()));
    }
}
