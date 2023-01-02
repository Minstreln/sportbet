import mysql.connector
  
  
   def criar_conexa(host, usuario, senha, banco):
    return mysql.connector.connect(host=host, user=usuario, password=senha, database=banco)
	
   def fechar_conexao(con):
    return con.close()   