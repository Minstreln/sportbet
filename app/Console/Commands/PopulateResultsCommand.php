<?php

namespace App\Console\Commands;

use App\Models\Match;
use App\Models\Result;
use App\Models\Resultado;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PopulateResultsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:getResults';

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
        $matchs = Match::all();
        foreach ($matchs as $match) {
            echo "\e[0;36;40m Obtendo resultados do evento: {$match->event_id} \e[0m\n";
            $result = $this->get(env('URL_BASE_BETS_API') . 'betfair/result?token=' . env('BETS_API_TOKEN') . '&event_id=' . $match->event_id);
            $result = json_decode($result);

            if (isset($result->results) && isset($result->results[0])) {
                echo "\e[0;36;40m Populando evento: {$match->event_id} \e[0m\n";
                $result = $result->results[0];
                $resultadoModel = Resultado::updateOrCreate([
                    'match_id' => $match->id
                ], [
                    'match_id' => $match->id,
                    'scores' => $result->ss ?? 0,
                    'resultado' => $result->ss ?? '0-0'
                ]);

                $resultModel = Result::updateOrCreate([
                  'match_id' => $match->id
                ], [
                    'match_id' => $match->id,
                    'home' => $result->home->name ?? 'NULL',
                    'away' => $result->away->name ?? 'NULL',
                    'date' => isset($result->time) ? Carbon::createFromTimestamp($result->time)->setTimezone('America/Sao_Paulo')->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                    'socore_home_ful_time' => $result->scores->{2}->home ?? 0,
                    'socore_away_ful_time' => $result->scores->{2}->away ?? 0,
                    'socore_home_half_time' => $result->scores->{1}->home ?? 0,
                    'socore_away_half_time' => $result->scores->{1}->away ?? 0,
                    'score_global' => $result->ss ?? 0,
                ]);
            } else {
                echo "\e[0;36;40m Propriedade results no evento: {$match->event_id} não existe! \e[0m\n";
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

}
