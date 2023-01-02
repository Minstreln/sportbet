<?php

namespace App\Console\Commands;

use App\Models\MainLeague;
use App\Models\Match;
use App\Models\Odd;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PopulateDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate DB with games';

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
        $this->populateMainLeagues();
    }

    protected function populateMainLeagues()
    {
        echo "\e[0;36;40m Inicializando sistema...! \e[0m\n";
        $sportsId = self::getGamesIds();

        foreach ($sportsId as $key => $sport) {
            echo "\e[0;36;40m Populando a partir de {$sport}...! \e[0m\n";
            $response = $this->get(env('URL_BASE_BETS_API') . 'league?token='. env('BETS_API_TOKEN') .'&sport_id=' .$key);
            if (empty($response)) {
                continue;
            }

            $response = json_decode($response);
            $pages = $response->pager->total/$response->pager->per_page;
            $pages = (int) round($pages);

            for ($page=1; $page <= $pages; $page++) {
                echo "\e[0;36;40m Populando página número {$page}...! \e[0m\n";
                $response = $this->get(env('URL_BASE_BETS_API') . 'league?token='. env('BETS_API_TOKEN') .'&sport_id=' .$key. '&page='.$page);
                $response = json_decode($response);
                if ($response->success == 1 && $response->pager->total > 0) {
                    foreach ($response->results as $data) {
                        echo "\e[0;36;40m Adicionando Liga {$data->name}...! \e[0m\n";
                        MainLeague::updateOrCreate([
                          'league_id' => $data->id,
                          'site_id' => env('ID_SITE')
                        ], [
                          'sport' => $sport,
                          'league' => $data->name,
                          'league_id' => $data->id,
                          'site_id' =>  env('ID_SITE')
                        ]);
                    }
                }
            }

            echo "\e[0;36;40m Buscando as upcoming... \e[0m\n";
            $upCommings = $this->get(env('URL_BASE_BETS_API') . 'betfair/sb/upcoming?token='. env('BETS_API_TOKEN') .'&sport_id=' .$key);
            $upCommings = json_decode($upCommings);
            $upCommingsPages = $upCommings->pager->total/$upCommings->pager->per_page;
            $upCommingsPages = (int) round($upCommingsPages);

            if (!isset($upCommings->success)) {
                continue;
            }

            for ($pageUpCommings=1; $pageUpCommings <= $upCommingsPages; $pageUpCommings++) {
                echo "\e[0;36;40m Buscando as upcoming página {$pageUpCommings}... \e[0m\n";
                echo "\e[0;36;40m Populando Match página número {$pageUpCommings}...! \e[0m\n";
                $upCommings = $this->get(env('URL_BASE_BETS_API') . 'betfair/sb/upcoming?token='. env('BETS_API_TOKEN') .'&sport_id=' .$key. '&page='.$pageUpCommings);
                $upCommings = json_decode($upCommings);

                if (!isset($upCommings->success)) {
                    continue;
                }

                foreach ($upCommings->results as $upComming) {
                    echo "\e[0;36;40m Consultnado resultdaos do evento {$upComming->id}... \e[0m\n";
                    $betFairResult = $this->get(env('URL_BASE_BETS_API') . 'betfair/result?token='. env('BETS_API_TOKEN') .'&event_id=' .$upComming->id);
                    $betFairResult = json_decode($betFairResult);

                    if (!isset($betFairResult->results)) {
                        continue;
                    }

                    $betFairResult = $betFairResult->results;
                    $betFairResult = $betFairResult[0];

                    if (!isset($betFairResult)) {
                        continue;
                    }

                    $dataConfronto = Carbon::createFromTimestamp($upComming->time)->format('Y-m-d h:S');

                    $lastMatachCreated = Match::updateOrCreate([
                        'event_id' => $upComming->id,
                        'our_event_id' => $upComming->our_event_id,
                    ], [
                        'sport_id' => $upComming->sport_id,
                        'our_event_id' => $upComming->our_event_id,
                        'event_id' => $upComming->id,
                        'league_id' => $upComming->league->id,
                        'sport_name' => $sport,
                        'visible' => "Sim",
                        'time_status' => $upComming->time_status,
                        'time' => $upComming->time,
                        'home' => $upComming->home->name,
                        'away' => $upComming->away->name,
                        'confronto' => $dataConfronto.$upComming->league->name.$upComming->home->name.$upComming->away->name,
                        'image_id_home' => $betFairResult->home->image_id ?? 0,
                        'image_id_away' => $betFairResult->away->image_id ?? 0,
                        'score' => $betFairResult->ss ?? 0,
                        'order' => 0,
                        'schedule' => 0,
                        'league' => $betFairResult->league->name ?? 'NULL',
                        'date' => Carbon::createFromTimestamp($upComming->time)->format('Y-m-d H:i:s'),
                    ]);

                    $getOddsResponse = $this->get(env('URL_BASE_BETS_API') . 'betfair/sb/event?token='. env('BETS_API_TOKEN') .'&event_id='.$upComming->id);
                    $getOddsResponse = json_decode($getOddsResponse);

                    if (isset($getOddsResponse->results) && !empty($getOddsResponse->results)) {
                        $getOddsResponseData = $getOddsResponse->results[0];
                        $markets = $getOddsResponseData->markets;
                        if (isset($getOddsResponseData->timeline)) {
                            echo "\e[0;36;40m Atualizando resultdaos do evento {$upComming->id}... \e[0m\n";
                            $lastMatachCreated = $lastMatachCreated::where('id', $lastMatachCreated->id)->update([
                                'halfTimeScoreHome' => $getOddsResponseData->timeline->score->home->halfTimeScore ?? 'NULL',
                                'halfTimeScoreAway' => $getOddsResponseData->timeline->score->away->halfTimeScore ?? 'NULL',
                                'fullTimeScoreHome' => ($getOddsResponseData->timeline->score->home->score - $getOddsResponseData->timeline->score->home->halfTimeScore) ?? 'NULL',
                                'fullTimeScoreAway' => ($getOddsResponseData->timeline->score->away->score - $getOddsResponseData->timeline->score->away->halfTimeScore) ?? 'NULL',
                                'numberOfCornersHome' => $getOddsResponseData->timeline->score->home->numberOfCorners ?? 'NULL',
                                'numberOfCornersAway' => $getOddsResponseData->timeline->score->away->numberOfCorners ?? 'NULL',
                                'numberOfYellowCardsHome' => $getOddsResponseData->timeline->score->home->numberOfYellowCards ?? 'NULL',
                                'numberOfYellowCardsAway' => $getOddsResponseData->timeline->score->away->numberOfYellowCards ?? 'NULL',
                                'numberOfRedCardsHome' => $getOddsResponseData->timeline->score->home->numberOfRedCards ?? 'NULL',
                                'numberOfRedCardsAway' => $getOddsResponseData->timeline->score->away->numberOfRedCards ?? 'NULL',
                            ]);
                        }

                        foreach ($markets as $market) {
                            echo "\e[0;36;40m Analisando markets do evento {$upComming->id}, e type: {$market->market->marketType}... \e[0m\n";
                            if (isset($market->market->marketType) && $market->market->marketType == "MATCH_ODDS") {
                                foreach ($market->market->runners as $key => $runners) {
                                    if (isset($market->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                                        Odd::create([
                                            'match_id'                  => $lastMatachCreated->id, //$match_id,
                                            'event_id'                  => $upComming->id,
                                            'mercado_name'              => 'Vencedor do Encontro',
                                            'odd'                       => strtr($runners->result->type, self::getTranslate()),
                                            'mercado_full_name'         => strtr($runners->result->type, self::getTranslate()),
                                            'cotacao'                   => $market->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                                            'status'                    => 1,
                                            'selectionId'               => $upComming->id . $runners->result->type . $market->market->marketType,
                                            'state'                     => $runners->runnerStatus,
                                            'stateMarc'                 => $market->market->marketStatus,
                                            'order'                     => 1,
                                            'header'                    => $key,
                                            'type'                      => "ao-vivo"
                                        ]);
                                    } else {
                                        Odd::create([
                                            'match_id'                  => $lastMatachCreated->id, //$match_id,
                                            'event_id'                  => $upComming->id,
                                            'mercado_name'              => 'Vencedor do Encontro',
                                            'odd'                       => strtr($runners->result->type, self::getTranslate()),
                                            'mercado_full_name'         => strtr($runners->result->type, self::getTranslate()),
                                            'cotacao'                   => 1,
                                            'status'                    => 1,
                                            'selectionId'               => $upComming->id . $runners->result->type . $market->market->marketType,
                                            'state'                     => $runners->runnerStatus,
                                            'stateMarc'                 => $market->market->marketStatus,
                                            'order'                     => 1,
                                            'header'                    => $key,
                                            'type'                      => "ao-vivo"
                                        ]);
                                    }
                                }
                            } elseif (isset($market->market->marketType) && $market->market->marketType == "BOTH_TEAMS_TO_SCORE") {
                                for ($i = 0; $i < count($market->market->runners); $i++) {
                                    Odd::create([
                                      'match_id'                  =>  $lastMatachCreated->id,
                                      'event_id'                  =>  $upComming->id,
                                      'mercado_name'              =>  'Ambas as equipes marcarão na partida',
                                      'odd'                       =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $market->market->runners[$i]->runnerName)),
                                      'mercado_full_name'         =>  str_replace('Yes', 'Ambas - Sim', str_replace('No', 'Ambas - Não', $market->market->runners[$i]->runnerName)),
                                      'cotacao'                   =>  0,
                                      'status'                    =>  0,
                                      'selectionId'               =>  $market->market->runners[$i]->selectionId,
                                      'state'                     =>  $market->market->runners[$i]->runnerStatus,
                                      'stateMarc'                 =>  $market->market->marketStatus,
                                      'order'                     =>  2,
                                      'header'                    =>  $market->market->runners[$i]->sortPriority,
                                      'type'                      =>  'ao-vivo'
                                    ]);
                                }

                                for ($i = 0; $i < count($market->runnerDetails); $i++) {
                                    $odd = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Ambas as equipes marcarão na partida')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                                    if ($odd) {
                                        $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                        $odd->status = 1;
                                        $odd->save();
                                    }
                                }
                            } elseif (isset($market->market->marketType) && $market->market->marketType == "DOUBLE_CHANCE") {
                                for ($i = 0; $i < count($market->market->runners); $i++) {
                                    Odd::create([
                                        'match_id'                  =>  $lastMatachCreated->id,
                                        'event_id'                  =>  $upComming->id,
                                        'mercado_name'              =>  'Chance Dupla',
                                        'odd'                       =>  strtr($market->market->runners[$i]->sortPriority, self::translateDupla()),
                                        'mercado_full_name'         =>  strtr($market->market->runners[$i]->sortPriority, self::translateDupla()),
                                        'cotacao'                   =>  0,
                                        'status'                    =>  0,
                                        'selectionId'               =>  $market->market->runners[$i]->selectionId,
                                        'state'                     =>  $market->market->runners[$i]->runnerStatus,
                                        'stateMarc'                 =>  $market->market->marketStatus,
                                        'order'                     =>  3,
                                        'header'                    =>  $market->market->runners[$i]->sortPriority,
                                        'type'                      =>  'ao-vivo'
                                    ]);

                                    for ($i = 0; $i < count($market->runnerDetails); $i++) {
                                        $odd = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Chance Dupla')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                                        if ($odd) {
                                            $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                            $odd->status = 1;
                                            $odd->save();
                                        }
                                    }
                                }
                            } elseif (isset($market->market->marketType) && $market->market->marketType == "CORRECT_SCORE") {
                                for ($i = 0; $i < count($market->market->runners); $i++) {
                                    $odd = Odd::create([
                                        'match_id'                  =>  $lastMatachCreated->id,
                                        'event_id'                  =>  $upComming->id,
                                        'mercado_name'              =>  'Placar Exato Tempo Completo',
                                        'odd'                       =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName),
                                        'mercado_full_name'         =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName),
                                        'cotacao'                   =>  0,
                                        'status'                    =>  0,
                                        'selectionId'               =>  $market->market->runners[$i]->selectionId,
                                        'state'                     =>  $market->market->runners[$i]->runnerStatus,
                                        'stateMarc'                 =>  $market->market->marketStatus,
                                        'order'                     =>  11,
                                        'type'                      =>  'ao-vivo'
                                    ]);
                                }

                                //Cotação
                                for ($i = 0; $i < count($market->runnerDetails); $i++) {
                                    $odd = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                                    if ($odd) {
                                        $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                        $odd->status = 1;
                                        $odd->save();
                                    }
                                }
                                $delete = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('status', 0)->delete();
                            } elseif (isset($market->market->marketType) && $market->market->marketType == "HALF_TIME_SCORE") {
                                for ($i = 0; $i < count($market->market->runners); $i++) {
                                    $odd = Odd::create([
                                        'match_id'                  =>  $lastMatachCreated->id,
                                        'event_id'                  =>  $upComming->id,
                                        'mercado_name'              =>  'Placar Exato (1T)',
                                        'odd'                       =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName) . " (1T)",
                                        'mercado_full_name'         =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName) . " (1T)",
                                        'cotacao'                   =>  0,
                                        'status'                    =>  0,
                                        'selectionId'               =>  $market->market->runners[$i]->selectionId,
                                        'state'                     =>  $market->market->runners[$i]->runnerStatus,
                                        'stateMarc'                 =>  $market->market->marketStatus,
                                        'order'                     =>  12,
                                        'type'                      =>  'ao-vivo'
                                    ]);
                                }

                                //Cotação
                                for ($i = 0; $i < count($market->runnerDetails); $i++) {
                                    $odd = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato (1T)')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                                    if ($odd) {
                                        $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                        $odd->status = 1;
                                        $odd->save();
                                    }
                                }

                                $delete = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato (1T)')->where('status', 0)->delete();
                            } elseif (isset($market->market->marketType) && $market->market->marketType == "2ND_HALF_CORRECT_SCORE") {
                                for ($i = 0; $i < count($market->market->runners); $i++) {
                                    $odd = Odd::create([
                                        'match_id'                  =>  $lastMatachCreated->id,
                                        'event_id'                  =>  $upComming->id,
                                        'mercado_name'              =>  'Placar Exato (2T)',
                                        'odd'                       =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName) . " (2T)",
                                        'mercado_full_name'         =>  str_replace(' - ', '-', $market->market->runners[$i]->runnerName) . " (2T)",
                                        'cotacao'                   =>  0,
                                        'status'                    =>  0,
                                        'selectionId'               =>  $market->market->runners[$i]->selectionId,
                                        'state'                     =>  $market->market->runners[$i]->runnerStatus,
                                        'stateMarc'                 =>  $market->market->marketStatus,
                                        'order'                     =>  13,
                                        'type'                      =>  'ao-vivo'
                                    ]);
                                }

                                //Cotação
                                for ($i = 0; $i < count($market->runnerDetails); $i++) {
                                    $odd = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato (2T)')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                                    if ($odd) {
                                        $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                                        $odd->status = 1;
                                        $odd->save();
                                    }
                                }
                                $delete = Odd::where('event_id', $upComming->id)->where('mercado_name', 'Placar Exato (2T)')->where('status', 0)->delete();
                            }
                        }
                    }
                }
            }
        }
        echo "\e[0;36;40m Ciclo Finalizado...! \e[0m\n";
    }

    protected function get($url)
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
      ));
      $response = curl_exec($curl);
      curl_close($curl);
      return $response;
    }

    protected static function getGamesIds()
    {
        return [
            1	=> 'Soccer',
            18 =>	'Basketball',
            13 => 'Tennis',
//            91 =>	'Volleyball',
//            78 =>	'Handball',
//            16 =>	'Baseball',
//            17 =>	'Ice Hockey',
//            14 =>	'Snooker',
//            12 =>	'American Football',
//            3 => 'Cricket',
//            83 =>	'Futsal',
//            15 =>	'Darts',
//            92 =>	'Table Tennis',
//            94 =>	'Badminton',
//            8 => 'Rugby Union',
//            19 =>	'Rugby League',
//            36 =>	'Australian Rules',
//            66 =>	'Bowls',
            9 =>'Boxing/UFC',
//            75 =>	'Gaelic Sports',
//            90 =>	'Floorball',
//            95 =>	'Beach Volleyball',
//            110 => 'Water Polo',
//            107 =>	'Squash',
//            151 =>	'E-sports'
        ];
    }

  protected static function getTranslate()
  {
      return [
          'HOME'              => 'Casa',
          'The Draw'          => 'Empate',
          'DRAW'              => 'Empate',
          'AWAY'              => 'Fora',
          'Over '             => 'Mais de ',
          ' Goals'            => '',
          'Under '            => 'Menos de ',
          '1st Half Over '    => 'Mais de ',
          '1st Half Under '   => 'Menos de ',
          'No Goalscorer'     => 'Sem Gols',
          '1'                 => 'Casa',
          '2'                 => 'Empate',
          '3'                 => 'Fora',
      ];
  }

  protected function translateDupla()
  {
      return [
          '1' => "Casa ou Empate",
          '2' => "Empate ou Fora",
          '3' => "Casa ou Fora"
      ];
  }
}
