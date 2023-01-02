		
<?php		
		/* Dependendia da classe*/ 
		/* Foi utilizado o conceito de query builder*/
		
		class Database{

			/**
			* 
			*/
			private $host;
			private $dbname;
			private $user;
			private $password;



			private $table;
			private $connection;

			/**
			* M�TODO PARA SELECIONAR QUAL BANCO SER� UTILIZADO * 

			* @param $host -> hostname da base de dados
			* @param $dbname -> nome do banco de dados
			* @param $user -> usuario do banco de dados
			* @param $password -> referente a senha do banco
			 */
			public function whereDB($host , $dbname, $user, $password){
				$this->host = $host;
				$this->dbname = $dbname;
				$this->user = $user;
				$this->password = $password;
			}

			/**
			* OBJETO INSTANCIADO NO CONSTRUTOR
			*/
			public function __construct($host, $dbname, $user, $password){
				$this->whereDB($host,$dbname,$user, $password);
				$this->setConnection();
			}

			/* M�todo respons�vel pela cria��o da conex�o com banco de dados*/ 
			private function setConnection(){
				try{
					$this->connection = new PDO('mysql:host='.$this->host.';dbname='.$this->dbname, $this->user,$this->password);
					$this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);  /* Encerra processo se der erro */ 
				}catch (PDOException $e) {
					die('ERROR: '.$e->getMessage());
				}
				catch(Exception $e){
					die('ERROR: '.$e->getMessage());
				}
			}

			/**
			* M�todo respons�vel por executar todas as query
			* @param string $query
			* @param array $params
			*/
			public function execute($query, $params = []){
				 try {
				 	 $statement = $this->connection->prepare($query);
				 	 $statement->execute($params);
				 	 return $statement;
				 } catch (PDOException $e) {
				 	die('ERROR: '.$e->getMessage());
				 }

			}

			/**
			* M�doto respons�vel por inserir valores no banco
			* @param array
			* @return integer
			*/
			public function insert($values){
				  /* Dados da query*/

				  $fields = array_keys($values);
				  $binds = array_pad([],count($fields),'?');
				  foreach($fields as $key => $field) {
					  $fields[$key] = '`'. $field . '`';
				  }
				  /* Montagem da query */
				  $query = 'INSERT INTO '.$this->table.' ('.implode(',',$fields).') values ('.implode(',', $binds).')';
				  $this->execute($query,array_values($values));
				  return $this->connection->lastInsertId();

			}	

			/**
			*	@param string $where 
			*	@param string $order
			*	@param string $limit
			*	@param string $fields
			*	@return PDOStatement
			*/

			public function select ($where = null, $order = null, $limit = null, $fields = '*'){
				/* DADOS DA QUERY*/
				$where = strlen($where) ? ' WHERE '. $where : '';
				$order = strlen($order) ? ' ORDER BY '. $order : '';
				$limit = strlen($limit) ? ' LIMIT '. $limit : '';


				/* Monta query */
				$query = 'SELECT '.$fields.' FROM '.$this->table.' '.$where.' '.$order.' '.$limit;
				return $this->execute($query);
			}

			/** 
			* M�TODO RESPONS�VEL POR EXECUTAR ATUALIZA��ES NO BANCO
			* 	@param string $where
			*	@param array $values [ field => value]
			*	@return boolean
			*/
			
			public function update($where, $values){
				//DADOS DA QUERY
				$fields = array_keys($values);

				// MONTA A QUERY
				$query = 'UPDATE '.$this->table.' SET '.implode('=?,', $fields). '=? WHERE '.$where;
				$this->execute($query, array_values($values));
				return true;
			}
			
			/**
			* M�TODO RESPONS�VEL POR EXCLUIR DADOS DO BANCO
			*	@param string $where
			*	@return boolean 
			*/ 
			public function delete($where){
				$query = 'DELETE FROM '.$this->table. ' WHERE '.$where;
				$this->execute($query);
				return true;
			}

			public function setTable($table){
				$this->table = $table;
			}
		}
		
	

?>