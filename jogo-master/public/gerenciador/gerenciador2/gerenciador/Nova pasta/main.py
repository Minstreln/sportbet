import pymysql
import json
import requests

def main():
    # Abrimos uma conexao com o banco de dados:
    conexao = pymysql.connect(db='csportesbet', user='starbetss', passwd='*Turionx2@', host='127.0.0.1')
    # Request
    r = requests.get('https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx')
	
    # Cria um cursor:
    cursor = conexao.cursor()

    #objeto =json.loads(r)

    json_obj = r.json()
    # json_obj = json.loads(r)

    for ord in json_obj["results"]:
     print("our_event_id:", ord["our_event_id"])
     print("sport_id:", ord["sport_id"])
     print("time:", ord["time"])
     print("time_status:", ord["time_status"])
     print("---")
	 
	 
  #  for ord in json_obj["results"]:
  #   print("league:", ord["league"][1])
	 
    # Executa o comando:
   #  cursor.execute("INSERT INTO matches (our_event_id, sport_id, event_id) values (%s,%s,%s)", (ord["our_event_id"], ord["sport_id"],ord["event_id"]))
 #   cursor.execute("INSERT INTO matches (our_event_id,sport_id) values (%s,%s)",(ord["our_event_id"], ord["sport_id"]))

    #cursor.execute("INSERT INTO matches (event_id, our_event_id, sport_id,	league_id, league_cc, sport_name, visible, score, time_status, order, schedule, date, league, home, image_id_home, confronto, away, image_id_away, created_at, updated_at, live_status, time, home_true, away_true, halfTimeScoreHome, halfTimeScoreAway, fullTimeScoreHome, fullTimeScoreAway, numberOfCornersHome, numberOfCornersAway, numberOfYellowCardsHome, numberOfYellowCardsAway, numberOfRedCardsHome, numberOfRedCardsAway) VALUES (y['event_id'], y['our_event_id'])")

    # Efetua um commit no banco de dados.
    # Por padrao, nao e efetuado commit automaticamente. Voce deve commitar para salvar
    # suas alteracoes.
    conexao.commit()

    # Finaliza a conexao
    conexao.close()
    print("Fim") 
 
 
if __name__ == '__main__':
    main() 

