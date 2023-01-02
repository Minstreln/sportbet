<?php

namespace App\Console\Commands;

use App\Models\Match;
use App\Models\Odd;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PopulateLiveDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:livePopulate';

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
        $sports = self::getGamesIds();

        foreach ($sports as $key => $sport) {
            $gamesInPlay = $this->get(env('URL_BASE_BETS_API') . 'betfair/sb/inplay?sport_id='.$key.'&token='.env('BETS_API_TOKEN'));
            $gamesInPlay = json_decode($gamesInPlay);

            if (isset($gamesInPlay->results)) {
                $results = $gamesInPlay->results;
                foreach ($results as $result) {
                  $dataConfronto = Carbon::createFromTimestamp($result->time)->format('Y-m-d h:S');
                  $startGameHour = Carbon::createFromTimestamp($result->time)->setTimezone('America/Sao_Paulo');
                  $lastMatachCreated = Match::updateOrCreate([
                      'event_id' => $result->id,
                      'our_event_id' => $result->our_event_id,
                  ], [
                      'sport_id' => $result->sport_id,
                      'our_event_id' => $result->our_event_id,
                      'event_id' => $result->id,
                      'league_id' => $result->league->id,
                      'sport_name' => $sport,
                      'visible' => "Sim",
                      'time_status' => $result->time_status,
                      'time' => $startGameHour->diffInMinutes(Carbon::now()->setTimezone('America/Sao_Paulo')),
                      'home' => $result->home->name,
                      'away' => $result->away->name,
                      'confronto' => $dataConfronto.$result->league->name.$result->home->name.$result->away->name,
                      'image_id_home' => $result->home->image_id ?? 0,
                      'image_id_away' => $result->away->image_id ?? 0,
                      'score' => $result->ss ?? 0,
                      'order' => 0,
                      'schedule' => 0,
                      'league' => $result->league->name ?? 'NULL',
                      'date' => Carbon::createFromTimestamp($result->time)->format('Y-m-d H:i:s'),
                  ]);
                  $lastMatachCreatedId = $lastMatachCreated->id;

                  $getOddsResponse = $this->get(env('URL_BASE_BETS_API') . 'betfair/sb/event?token='. env('BETS_API_TOKEN') .'&event_id='.$result->id);
                  $getOddsResponse = json_decode($getOddsResponse);

                  if (isset($getOddsResponse->results) && !empty($getOddsResponse->results)) {
                    $getOddsResponseData = $getOddsResponse->results[0];
                    $markets = $getOddsResponseData->markets;

                    foreach ($markets as $market) {
                      if (isset($market->market->marketType) && $market->market->marketType == "MATCH_ODDS") {
                        foreach ($market->market->runners as $key => $runners) {
                          if (isset($market->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds)) {
                            Odd::updateOrCreate([
                              'match_id'                  => $lastMatachCreatedId, //$match_id,
                              'event_id'                  => $result->id,
                            ],[
                              'match_id'                  => $lastMatachCreatedId, //$match_id,
                              'event_id'                  => $result->id,
                              'mercado_name'              => 'Vencedor do Encontro',
                              'odd'                       => strtr($runners->result->type, self::getTranslate()),
                              'mercado_full_name'         => strtr($runners->result->type, self::getTranslate()),
                              'cotacao'                   => $market->runnerDetails[$key]->runnerOdds->decimalDisplayOdds->decimalOdds,
                              'status'                    => 1,
                              'selectionId'               => $result->id . $runners->result->type . $market->market->marketType,
                              'state'                     => $runners->runnerStatus,
                              'stateMarc'                 => $market->market->marketStatus,
                              'order'                     => 1,
                              'header'                    => $key,
                              'type'                      => "ao-vivo"
                            ]);
                          } else {
                            Odd::updateOrCreate([
                              'match_id'                  => $lastMatachCreatedId, //$match_id,
                              'event_id'                  => $result->id,
                            ],[
                              'match_id'                  => $lastMatachCreatedId, //$match_id,
                              'event_id'                  => $result->id,
                              'mercado_name'              => 'Vencedor do Encontro',
                              'odd'                       => strtr($runners->result->type, self::getTranslate()),
                              'mercado_full_name'         => strtr($runners->result->type, self::getTranslate()),
                              'cotacao'                   => 1,
                              'status'                    => 1,
                              'selectionId'               => $result->id . $runners->result->type . $market->market->marketType,
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
                          Odd::updateOrCreate([
                            'match_id'                  => $lastMatachCreatedId, //$match_id,
                            'event_id'                  => $result->id,
                          ],[
                            'match_id'                  =>  $lastMatachCreatedId,
                            'event_id'                  =>  $result->id,
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
                          $odd = Odd::where('event_id', $result->id)->where('mercado_name', 'Ambas as equipes marcarão na partida')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                          if ($odd) {
                            $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                            $odd->status = 1;
                            $odd->save();
                          }
                        }
                      } elseif (isset($market->market->marketType) && $market->market->marketType == "DOUBLE_CHANCE") {
                        for ($i = 0; $i < count($market->market->runners); $i++) {
                          Odd::updateOrCreate([
                            'match_id'                  => $lastMatachCreatedId, //$match_id,
                            'event_id'                  => $result->id,
                          ],[
                            'match_id'                  =>  $lastMatachCreatedId,
                            'event_id'                  =>  $result->id,
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
                            $odd = Odd::where('event_id', $result->id)->where('mercado_name', 'Chance Dupla')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                            if ($odd) {
                              $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                              $odd->status = 1;
                              $odd->save();
                            }
                          }
                        }
                      } elseif (isset($market->market->marketType) && $market->market->marketType == "CORRECT_SCORE") {
                        for ($i = 0; $i < count($market->market->runners); $i++) {
                          $odd = Odd::updateOrCreate([
                            'match_id'                  => $lastMatachCreatedId, //$match_id,
                            'event_id'                  => $result->id,
                          ],[
                            'match_id'                  =>  $lastMatachCreatedId,
                            'event_id'                  =>  $result->id,
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
                          $odd = Odd::where('event_id', $result->id)->where('mercado_name', 'Placar Exato Tempo Completo')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                          if ($odd) {
                            $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                            $odd->status = 1;
                            $odd->save();
                          }
                        }
                      } elseif (isset($market->market->marketType) && $market->market->marketType == "HALF_TIME_SCORE") {
                        for ($i = 0; $i < count($market->market->runners); $i++) {
                          $odd = Odd::updateOrCreate([
                            'match_id'                  => $lastMatachCreatedId, //$match_id,
                            'event_id'                  => $result->id,
                          ],[
                            'match_id'                  =>  $lastMatachCreatedId,
                            'event_id'                  =>  $result->id,
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
                          $odd = Odd::where('event_id', $result->id)->where('mercado_name', 'Placar Exato (1T)')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                          if ($odd) {
                            $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                            $odd->status = 1;
                            $odd->save();
                          }
                        }
                      } elseif (isset($market->market->marketType) && $market->market->marketType == "2ND_HALF_CORRECT_SCORE") {
                        for ($i = 0; $i < count($market->market->runners); $i++) {
                          $odd = Odd::updateOrCreate([
                            'match_id'                  => $lastMatachCreatedId, //$match_id,
                            'event_id'                  => $result->id,
                          ],[
                            'match_id'                  =>  $lastMatachCreatedId,
                            'event_id'                  =>  $result->id,
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
                          $odd = Odd::where('event_id', $result->id)->where('mercado_name', 'Placar Exato (2T)')->where('selectionId', $market->runnerDetails[$i]->selectionId)->first();
                          if ($odd) {
                            $odd->cotacao = $market->runnerDetails[$i]->runnerOdds->decimalDisplayOdds->decimalOdds;
                            $odd->status = 1;
                            $odd->save();
                          }
                        }
                      }
                    }
                  }
                }
            }
        }
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
//      18 =>	'Basketball',
//      13 => 'Tennis',
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
//      9 =>'Boxing/UFC',
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
      ' Goals'            => 'Gol',
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
