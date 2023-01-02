<?php 

	
	
	class RetornoAPI {
	 
	   
	   public $token = '78614-HWTKKepUL8Ufpx';		
	    
		public function BetfairResult($event_id){
			
			      $curl = curl_init();
				  curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.b365api.com/v1/betfair/result?token=".$this->token  ."&event_id=". $event_id,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 10,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_POSTFIELDS => "",
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  return  $response;
				}
			
		}
		public function carregarLigas($sport_id){

			  $curl = curl_init();
			  curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/league?token=".$this->token."&sport_id=".$sport_id,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
			
		}
		public function Odds($event_id){
			
			$curl = curl_init();
			curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v2/event/odds/summary?token=".$this->token."&event_id=".$event_id,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 10,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
		}
			
		public function TimeLIne($event_id){
							
				  $curl = curl_init();
				  curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.b365api.com/v1/betfair/timeline?token=".$this->token ."&event_id=".$event_id,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_POSTFIELDS => "",
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  return $response;
				}			
		}
		
	    public function ExchangeEvent($event_id){
			 $curl = curl_init();
  			  curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/betfair/ex/event?token=".$this->token ."&event_id=".$event_id,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
			
		}
		
		public function UpCommingExchange($sport_id){
			  $curl = curl_init();
			  curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/betfair/ex/upcoming?sport_id=". $sport_id ."&token=".$this->token,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
		}
		
		public function ExchangeInPlay($sport_id){
			
			  $curl = curl_init();
			  curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/betfair/ex/inplay?sport_id=".$sport_id."&token=".$this->token,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
		}
		
		public function SportBookInPlay($sport_id){
			
			      $curl = curl_init();
				  curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.b365api.com/v1/betfair/sb/inplay?sport_id=".$sport_id ."&token=".$this->token,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_POSTFIELDS => "",
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  return $response;
				}
		}
		
		public function Event($event_id){
			
				$curl = curl_init();
				curl_setopt_array($curl, [
				  CURLOPT_URL => "https://api.b365api.com/v1/betfair/sb/event?token=".$this->token ."&event_id=".$event_id,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "GET",
				  CURLOPT_POSTFIELDS => "",
				]);

				$response = curl_exec($curl);
				$err = curl_error($curl);

				curl_close($curl);

				if ($err) {
				  echo "cURL Error #:" . $err;
				} else {
				  return $response;
				}
		}
		
		public function InComming($sport_id){
			
			$curl = curl_init();
			curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/betfair/sb/upcoming?sport_id=".$sport_id ."&token=".$this->token,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
		}
		
		public function InPlay($sport_id){
			  
			  $curl = curl_init();
			  curl_setopt_array($curl, [
			  CURLOPT_URL => "https://api.b365api.com/v1/betfair/sb/inplay?sport_id=".$sport_id ."&token=".$this->token,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  return $response;
			}
			
		}

		public function ListagemEventos($sport_id){

			$curl = curl_init();
			curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.b365api.com/v1/events/inplay?sport_id=".$sport_id ."&token=".$this->token,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			echo "cURL Error #:" . $err;
			} else {
			return $response;
			}
		}
		
		public function resumoOdds($event_id){
			
			$curl = curl_init();
			curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.b365api.com/v2/event/odds/summary?token=".$this->token."&event_id=".$event_id,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			echo "cURL Error #:" . $err;
			} else {
			return $response;
			}

		}
	}







?>