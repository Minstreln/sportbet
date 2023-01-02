<?php


//$pdo = new PDO("mysql:host=localhos;dbname=csportsbets", "starbetss", "*Turionx2@"); 


$url = 'https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx';

//$url = 'https://api.b365api.com/v1/betfair/result?token=78614-HWTKKepUL8Ufpx&event_id=1792877';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
if ($data === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error occured during curl exec. Additioanl info: ' . var_export($info));
}
curl_close($ch);
print_r( json_decode($data) );
