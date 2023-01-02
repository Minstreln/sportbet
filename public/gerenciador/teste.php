<?php

$url = 'https://api.b365api.com/v1/events/ended?token=63430-NjmjDstJUPsAnB&sport_id=1';
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