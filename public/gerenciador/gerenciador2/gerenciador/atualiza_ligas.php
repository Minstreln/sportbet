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
	 $conta_registro = 0;
	 
     for($i=0; $i < $valor->pager->total; $i++)	
	 {
		
		if(!empty($resultado[$i]->name) AND !empty($resultado[$i]->id))
		{
			$nome_liga = "".$resultado[$i]->name;
		    $id_liga = $resultado[$i]->id;
			
			echo "<br> ID:".$id_liga;
	        echo "<br> Nome - Liga :".$nome_liga;
			$controller->cadastrarLiga($sport,$nome_liga,$id_liga,$site_id,$data_criacao,$data_criacao);
		}
		  
	 }

?>