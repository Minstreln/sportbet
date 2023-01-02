<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Match;
use App\Models\Odd;
use App\Models\BlockLeague;
use App\Models\BlockMatch;
use App\Models\Configuracao;
use App\Events\LiveFutebol;

class Live extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:live';

    private $leaguesBloqueadas;
    private $matchsBlock;
    private $configuration;

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
    public function __construct(
        Configuracao $configuration,
        BlockLeague $leaguesBloqueadas,
        BlockMatch $matchsBlock
        )
    {
        parent::__construct();
        $this->configuration        = $configuration;
        $this->leaguesBloqueadas    = $leaguesBloqueadas;
        $this->matchsBlock          = $matchsBlock;
        $this->matchsBloqueadas     = array();
        $this->arr                  = array();
        $this->leagueBloqueadas     = array();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */


    public function handle()
    {
        
        $live = $this->configuration::where('site_id', env('ID_SITE'))->first();

        $this->leagueBloqueadas = $this->leaguesBloqueadas::select('league')
                                                          ->where('site_id', env('ID_SITE'))
                                                          ->get();

        $matchsBloqueadas = $this->matchsBlock::select('event_id')
                                                    ->where('site_id', env('ID_SITE'))
                                                    ->get();

        $leagues = Match::select('league')
                        ->where('time_status', 1)
                        ->where('time', '<=',  $live->time_live)
                        ->whereNotIn('league', $this->leagueBloqueadas)
                        ->whereNotIn('event_id', $matchsBloqueadas)
                        ->groupBy('league')
                        ->orderBy('league', 'asc')
                        ->get();



        $i = 0;
        foreach ($leagues  as $league) {

            $this->arr[$i]['league'] = $league->league;

            $matchs = Match::where('league', $league->league)
                            ->whereNotIn('event_id', $matchsBloqueadas)
                            ->where('time', '<=',  $live->time_live)
                            ->where('time_status', 1)
                            ->with('odds')
                            ->get();
            $j = 0;
            foreach ($matchs as $match) {
                $this->arr[$i]['match'][$j]['id'] = $match->id;
                $this->arr[$i]['match'][$j]['event_id'] = $match->event_id;
                $this->arr[$i]['match'][$j]['sport'] = $match->sport_name;
                $this->arr[$i]['match'][$j]['confronto'] = $match->confronto;
                $this->arr[$i]['match'][$j]['home'] = $match->home;
                $this->arr[$i]['match'][$j]['image_id_home'] = $match->image_id_home;
                $this->arr[$i]['match'][$j]['away'] = $match->away;
                $this->arr[$i]['match'][$j]['image_id_away'] = $match->image_id_away;
                $this->arr[$i]['match'][$j]['score'] = $match->score;
                $this->arr[$i]['match'][$j]['date'] = $match->date;
                $this->arr[$i]['match'][$j]['time'] = $match->time;
                $this->arr[$i]['match'][$j]['halfTimeScoreHome'] = $match->halfTimeScoreHome;
                $this->arr[$i]['match'][$j]['halfTimeScoreAway'] = $match->halfTimeScoreAway;
                $this->arr[$i]['match'][$j]['fullTimeScoreHome'] = $match->fullTimeScoreHome;
                $this->arr[$i]['match'][$j]['fullTimeScoreAway'] = $match->fullTimeScoreAway;
                $this->arr[$i]['match'][$j]['numberOfCornersHome'] = $match->numberOfCornersHome;
                $this->arr[$i]['match'][$j]['numberOfCornersAway'] = $match->numberOfCornersAway;
                $this->arr[$i]['match'][$j]['numberOfYellowCardsHome'] = $match->numberOfYellowCardsHome;
                $this->arr[$i]['match'][$j]['numberOfYellowCardsAway'] = $match->numberOfYellowCardsAway;
                $this->arr[$i]['match'][$j]['numberOfRedCardsHome'] = $match->numberOfRedCardsHome;
                $this->arr[$i]['match'][$j]['numberOfRedCardsAway'] = $match->numberOfRedCardsAway;
                $count = Odd::where('match_id', $match->id)->count();
                $this->arr[$i]['match'][$j]['count_odd'] = $count;

                if ($count == 0 || count($match->odds) == 0) {
                    for ($q = 0; $q < 3; $q++) {
                        $this->arr[$i]['match'][$j]['odds'][$q]['id'] = $match->event_id . $match->id . $q;
                        $this->arr[$i]['match'][$j]['odds'][$q]['group_opp'] = 'Vencedor do Encontro';
                        $this->arr[$i]['match'][$j]['odds'][$q]['odd'] = $q;
                        $this->arr[$i]['match'][$j]['odds'][$q]['cotacao'] = 0;
                    }
                } else {

                    $q = 0;
                    foreach ($match->odds as $odd) {
                        $redu = $odd->cotacao - 1;
                        $odd_final = ($redu * $live->cotacao_live / 100) + $redu + 1;
                        $this->arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;
                        $this->arr[$i]['match'][$j]['odds'][$q]['id'] = $odd->id;
                        $this->arr[$i]['match'][$j]['odds'][$q]['group_opp'] = $odd->mercado_name;
                        $this->arr[$i]['match'][$j]['odds'][$q]['event_id'] = $match->event_id;
                        $this->arr[$i]['match'][$j]['odds'][$q]['odd'] = $odd->odd;
                        $this->arr[$i]['match'][$j]['odds'][$q]['cotacao'] = round($odd_final, 2);
                        $this->arr[$i]['match'][$j]['odds'][$q]['cotacaoOriginal'] = $odd->cotacao;
                        $this->arr[$i]['match'][$j]['odds'][$q]['type'] = $odd->type;

                        $q++;
                    }
                }
                $j++;
            }





            $i++;
        }

        broadcast(new  LiveFutebol($this->arr));

    }
}
