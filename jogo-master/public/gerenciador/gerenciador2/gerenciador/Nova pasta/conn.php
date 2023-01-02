// conn.php
<?php

// error_reporting(E_ALL ^ E_DEPRECATED);
// $conn = @mysql_connect('179.188.16.221','carlo_ilabs','zTf~f982');
// Usamos a versão do php 7.0.33

$server   = "localhost";
$database = "csportebet";
$username = "starbetss";
$password = "*Turionx2@";

// Create connection
$conn = new mysqli($server, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* change character set to utf8 */
//if (!$conn->set_charset("utf8")) {
//    printf("Error loading character set utf8: %s\n", $conn->error);
//    exit();
//} else {
//    printf("Current character set: %s\n", $conn->character_set_name());
//}

echo "Connected successfully";
mysqli_close($conn);
//mysql_select_db('carlos_erp', $conn);

?>