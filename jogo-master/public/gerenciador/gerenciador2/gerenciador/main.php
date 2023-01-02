

<?php


	 include("bets_controladora.php");
     include("bets.php");

	 $sport_id = 1;

	 $controller = new ResultadoController();
	 $obj = new RetornoAPI();
	 $resposta = $obj->InComming($sport_id);
     $valor = json_decode($resposta);
     var_dump($resposta);
      exit;
	 echo "<br> <br><br><br>";
	 $resultado = $valor->results;


     for($i=0; $i < $valor->pager->total; $i++)
	//  for($i=0; $i< 2; $i++ )
	 {
			// var_dump($data_confronto);
			 echo '<br>';
             $schedule = 0;
			 $order = 0;
			 $time = 0;
			 $visible = "true";
			 $data_atual = date('Y-m-d H:i');
			 //$ultima_data_valida =  date('Y-m-d', strtotime('-3 days', strtotime($data_atual)));

			 $data_confronto = date('Y-m-d h:s',$resultado[$i]->time);
			// echo "Data 1 <br>".$ultima_data_valida;

		   if(strtotime($data_confronto) >= strtotime('-1 day', strtotime($data_atual)))
		   {
			 /* Nova Consulta há API*/
			 $consulta = $obj->BetfairResult($resultado[$i]->id);
			 $resultado_consulta = json_decode($consulta);
			 $sport_book = $resultado_consulta->results;
			 $liga_sport_book = $sport_book[0]->league;
			 $home_sport_book = $sport_book[0]->home;
			 $away_sport_book = $sport_book[0]->away;

			 $image_id_home = $home_sport_book->image_id;
			 $image_id_away = $away_sport_book->image_id;

			 echo "<br> League CC ".$liga_sport_book->cc. "<br>";
			 echo "Our Event ID: ". $resultado[$i]->our_event_id. "<br>";
			 echo "ID: ".$resultado[$i]->id. "<br>";
			 echo "Sport ID: ".$resultado[$i]->sport_id. "<br>";
			 echo "Time Status: ".$resultado[$i]->time_status. "<br>";
			 echo "Image id_home : ".$image_id_home;
			 echo "Image id_away : ".$image_id_away;
			 echo "<br>";
			 $home = $resultado[$i]->home;

					   echo "Time da Casa <br>";
					   echo $home->name. " <br>" ;
					   echo $home->id .   " <br>";
					   echo "<br>";

			 $away = $resultado[$i]->away;
					   echo "Time  Visitante <br>";
					   echo $away->name. "<br>";
					   echo $away->id. "<br>";
					   echo "<br>";

			$league = $resultado[$i]->league;
					echo "Nome da Liga ".$league->name. "<br>";
					echo "ID da Liga ".$league->id. "<br>";


			$confronto = $data_confronto.$league->name.$home->name.$away->name;
			echo "Confronto: ".$confronto. "<br>";

			$create_at = $data_confronto;
			$update_at = NULL;
			$home_true = $home->name;
			$away_true = $away->name;

			$numberOfComersAway       = NULL;
			$numberOfCornersHome      = NULL;
			$numberOfRedCardsAway     = NULL;
			$numberOfRedCardsHome     = NULL;
			$numberOfYellowCardsAway  = NULL;
			$numberOfYellowCardsHome  = NULL;
			$score = $sport_book[0]->ss;

			/* Aqui será gravado a consulta no Banco*/
			$responseSave = $controller->cadastrar($resultado[$i]->id, $resultado[$i]->our_event_id, $sport_id, $league->id,
			$liga_sport_book->cc,"Futebol",$visible, $score, $resultado[$i]->time_status, $order,$schedule,
			$data_confronto,$league->name, $home->name, $image_id_home, $confronto, $away->name,$image_id_away,
			$create_at, $update_at, NULL, $time, $home->name, $away->name,NULL,NULL,NULL,NULL,NULL,NULL,
			NULL,NULL,NULL,NULL);

		   }

	 }
	  echo "Foram Salvas as informações no Banco";
?>
