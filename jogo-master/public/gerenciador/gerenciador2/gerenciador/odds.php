
<?php 


include("bets_controladora.php");
include("bets.php");




            /*
                Order Sumary Cotações das Odds

            */


            /* Struct
                 1 - Lista todos os eventos da lista
                 2 - Busca as odds de cada event_id 
                 3 - Atualiza as informações na tabela

            */ 

    $sport_id = 1; /* Futebol */
    $sport  = "Futebol";
    $site_id = "csportes";
    $controller = new ResultadoController();
    $obj = new RetornoAPI();

    $resultado = $obj->ListagemEventos($sport_id);
    $valor = json_decode($resultado);

    $eventos = $valor->results;
    
    $json  = "[";
    
    $controle = false;
    for($i=0; $i < $valor->pager->total; $i++)	 
    {

     $id = $eventos[$i]->id; /* event_id */
        
       
         $json = $json. ' { ';
         $json = $json.'"match_id":'.$id. ',';
         $league = $eventos[$i]->league; 
         $away = $eventos[$i]->away;
         $home = $eventos[$i]->home;
        
         $image_id_away = $away->image_id;
         $image_id_home = $home->image_id;
         $data_confronto = date('Y-m-d h:s',$eventos[$i]->time);
         $confronto = $data_confronto.$league->name.$home->name.$away->name;


         $json = $json.'"id": '.$id. ',';
         $json = $json.'"away" : "'.$away->name. '",' ; 
         $json = $json.'"date": "'.$data_confronto.'"'.',';
         $json = $json.'"home": "'.$home->name.'",';


         
         $response = $obj->resumoOdds($id);
         $results = json_decode($response);
         $controle = true;
        
         $resultado = $results->results;
         $corretora = $resultado->Bet365;
         $odds  = $corretora->odds;

          $start = $odds->start;
      
         
          $kickoff = $odds->kickoff;
          $end = $odds->end;
      
         $json = $json.'"odds": {';
         $json = $json. '"start": {';
         
         

         $json = $json.'} ,';

         $json = $json. '"kickoff": {';


         $json = $json.'} ,';
         
         $json = $json. '"end": {';


          $json = $json.'}';
        

         $json = $json.'},';

         $json = $json.'"sport": "'.$sport.'",';
         $json = $json. '"event_id": "'.$id.'",';
         $json = $json.'"confronto": "'.$confronto.'"'.',';
         $json = $json.'"image_id_away": "'.$image_id_away.'",';
         $json = $json.'"image_id_home": "'.$image_id_home.'"';
 
       
          $json = $json.'} ';
          
          if($i < $valor->pager->total-1)
          {
               $json = $json.',';
          }
           
          
        
       
         echo "<br>";
        

    }
    $json = $json."]";

    echo $json;
    $controller->cadastrarOdds($json,$site_id,date('Y-m-d H:s'),date('Y-m-d H:s'));

?>


