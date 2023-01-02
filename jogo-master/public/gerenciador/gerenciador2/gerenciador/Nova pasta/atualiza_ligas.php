<?php 
 
	 include("bets_controladora.php");
     include("bets.php");
	 
	 $league_id = 1; /* Substituir por GET*/
	 
	 $response = new RetornoAPI();
	 $ligas = $response->carregarLigas($league_id);
	 $valor = json_decode($ligas);	
	 $controller = new ResultadoController();
	 
	 
	 $resultado = $valor->results;
	 $site_id = "csport";
	 $data_criacao = date('Y-m-d H:s');
	 $sport = "Futebol";
	 
     for($i=0; $i < $valor->pager->total; $i++)	
	 {
		 echo $resultado[$i]->id;
		 echo $resultado[$i]->name;
		 $controller->cadastrarLiga($sport,$league->name,$league_id,$site_id,$data_criacao,$data_criacao);
	 }
?>