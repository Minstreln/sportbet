import json
import requests

def main():

  url = ('https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx')
  response = requests.get(url)
  matches = json.loads(response.text)

data = json.loads(our_event_id)  
print(type(data['our_event_id']))
  
  
#for iten in content["id"]["sport_id"]["event_id"]["our_event_id"]["sport_id"]["league_id"]["league_cc"]["sport_name"]["visible"]["score"]["time_status"]["order"]["schedule"]["date"]["league"]["home"]["image_id_home"]["confronto"]["away"]["image_id_away"]["created_at"]["updated_at"]["live_status"]["time"]["home_true"]["away_true"]["halfTimeScoreHome"]["halfTimeScoreAway"]["fullTimeScoreHome"]["fullTimeScoreAway"]["numberOfCornersHome"]["numberOfCornersAway"]["numberOfYellowCardsHome"]["numberOfYellowCardsAway"]["numberOfRedCardsHome"]["numberOfRedCardsAway"]
  
#print(matches['sport_id'][0]['league_id'])


if __name__ == '__main__':
   main() 



#for iten in content["id"]["sport_id"]["event_id"]["our_event_id"]["sport_id"]["league_id"]["league_cc"]["sport_name"]["visible"]["score"]["time_status"]["order"]["schedule"]["date"]["league"]["home"]["image_id_home"]["confronto"]["away"]["image_id_away"]["created_at"]["updated_at"]["live_status"]["time"]["home_true"]["away_true"]["halfTimeScoreHome"]["halfTimeScoreAway"]["fullTimeScoreHome"]["fullTimeScoreAway"]["numberOfCornersHome"]["numberOfCornersAway"]["numberOfYellowCardsHome"]["numberOfYellowCardsAway"]["numberOfRedCardsHome"]["numberOfRedCardsAway"]

