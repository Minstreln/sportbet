<?php 

  
 $HOST = '127.0.0.1';
 $USER = 'starbetss';
 $PASSWORD = '*Turionx2@';
 $DBNAME = 'csportesbet';

$conexao = mysqli_connect($HOST,$USER,$PASSWORD,$DBNAME);

	if(!$conexao){
		echo " Falha na conexão com banco de dados";
	}
	else{
		echo "Conexão estabelecida com sucesso";
	}




?>