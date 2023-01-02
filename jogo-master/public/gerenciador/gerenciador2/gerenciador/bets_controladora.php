<?php

   /* acabou a bateria do cel ... só um minuto tranquilo*/
    require 'banco.php';

    class ResultadoController{


	 const HOST     = '127.0.0.1';
	 const DBNAME   = 'csportesbet';
	 const PASSWORD     = '*Turionx2@';
	 const USER = 'root';

	 public $db;
	public function __construct(){
		$this->db = new Database(self::HOST,self::DBNAME,self::USER,self::PASSWORD);
	}

    public function insert($away , $away_true, $confronto, $created_at, $date, $event_id, $fullTimeScoreAway,
 	$fullTimeScoreHome, $halfTimeScoreHome, $home, $home_true, $image_id_away, $image_id_home, $leagle, $leagle_cc,
	$leagle_id, $live_status, $numberOfComersAway, $numberOfCornersHome, $numberOfRedCardsAway, $numberOfRedCardsHome,
	$numberOfYellowCardsAway, $numberOfYellowCardsHome, $order, $our_event_id, $schedule, $score, $sport_id,
	$sport_name, $time, $time_status,  $updated_at, $visible)
    {


			$sql = " INSERT INTO matches (
            away,away_true, confronto, created_at, date, event_id , fullTimeScoreAway , fullTimeScoreHome , halfTimeScoreAway,
			halfTimeScoreHome, home , home_true, image_id_away , image_id_home, league, league_cc, league_id , league_id, live_status
			numberOfComersAway, numberOfCornersHome , numberOfRedCardsAway , numberOfRedCardsHome, numberOfYellowCardsAway ,
			numberOfYellowCardsAway , numberOfYellowCardsHome, order , our_event_id , schedule , score , sport_id , sport_name,
			time, time_status, updated_at , visible)

			values ( '$away' , '$away_true', '$confronto', '$created_at', '$date', '$event_id', '$fullTimeScoreAway',
			'$fullTimeScoreHome', '$halfTimeScoreHome', '$home', '$home_true', '$image_id_away', '$image_id_home', '$leagle', '$leagle_cc',
			'$leagle_id', '$live_status', '$numberOfComersAway', '$numberOfCornersHome', '$numberOfRedCardsAway', '$numberOfRedCardsHome',
			'$numberOfYellowCardsAway', '$numberOfYellowCardsHome', '$order', '$our_event_id', '$schedule', '$score', '$sport_id',
			'$sport_name', '$time', '$time_status',  '$updated_at', '$visible')";


			echo "################################<br>";
			echo $sql;

			if(mysqli_query($conexao,$sql))
  			{

  			}
			else{
				echo "Erro ao gravar as informa??es!!";
			}
	    }
		public function cadastrarLiga($sport, $league, $league_id, $site_id, $created_at, $updated_at){

			$this->db->setTable('main_leagues');
			$this->db->insert([
				'sport' 		=> $sport,
				'league' 		=> $league,
				'league_id' 	=> $league_id,
				'site_id' 		=> $site_id,
				'created_at' 	=> $created_at,
				'updated_at' 	=> $updated_at
			]);
			echo "<pre>"; 	print_r($this); echo "</pre>";
		}


	    public function cadastrar($event_id,$our_event_id, $sport_id, $league_id,
								  $league_cc,$sport_name,$visible, $score,$time_status,
								  $order,$schedule,$date,$league,$home,$image_id_home,
								  $confronto,$away,$image_id_away,$created_at,$updated_at,
								  $live_status, $time, $home_true, $away_true,
								  $halfTimeScoreHome, $halfTimeScoreAway, $fullTimeScoreHome,
								  $fullTimeScoreAway, $numberOfCornersHome,$numberOfCornersAway,
								  $numberOfYellowCardsHome,$numberOfYellowCardsAway,$numberOfRedCardsHome,
								  $numberOfRedCardsAway){

			   $this->db->setTable('matches');

		       $insert = $this->db->insert([

  				'event_id' 		=> $event_id,
				'our_event_id' 	=> $our_event_id,
				'sport_id'		=> $sport_id,
				'league_id'		=> $league_id,
				'league_cc'		=> $league_cc,
				'sport_name'	=> $sport_name,
				'visible'		=> $visible,
				'score'			=> $score,
				'time_status'	=> (int) $time_status,
				'order'			=> $order,
				'schedule'		=> $schedule,
				'date'			=> $date,
				'league'		=> $league,
				'home'			=> $home,
				'image_id_home' => $image_id_home,
				'confronto'		=> $confronto,
				'away'			=> $away,
				'image_id_away' => $image_id_away,
				'created_at'	=> $created_at,
				'updated_at'	=> $updated_at,
				'live_status'   => $live_status,
				'time'			=> $time,
				'home_true'		=> $home_true,
				'away_true'		=> $away_true,
				'halfTimeScoreHome' => $halfTimeScoreHome,
				'halfTimeScoreAway' => $halfTimeScoreAway,
				'fullTimeScoreHome'       => $fullTimeScoreHome,
				'fullTimeScoreAway' 	  => $fullTimeScoreAway,
				'numberOfCornersHome'     => $numberOfCornersHome,
				'numberOfCornersAway' 	  => $numberOfCornersAway,
				'numberOfYellowCardsHome' => $numberOfYellowCardsHome,
				'numberOfYellowCardsAway' => $numberOfYellowCardsAway,
				'numberOfRedCardsHome' 	  => $numberOfRedCardsHome,
				'numberOfRedCardsAway'    => $numberOfRedCardsAway
		]);
		echo "<pre>"; 	print_r($this); echo "</pre>";
		return true;
		}


		public function cadastrarOdds($dados, $site_id, $created_at, $update_at){

			$this->db->setTable('afer_tomorow_match_flashes');

			$update = $this->db->update("site_id = 	'".$site_id."'",
				[
					'dados'      => $dados,
					'created_at' => $created_at,
					'updated_at'  => $update_at
				]
			);
		//	echo "<pre>"; 	print_r($this); echo "</pre>";
			return true;
		}

	}









 ?>
