	#! / usr / bin / env python3

	# Import json module
import json
import requests


def main():
	  r = requests.get('https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx')
	  

import json
 
json_input = '{"persons": [{"name": "Brian", "city": "Seattle"}, {"name": "David", "city": "Amsterdam"} ] }'

try:
    decoded = json.loads(json_input)
 
    # Access data
    for x in decoded['persons']:
        print x['name']
 
except (ValueError, KeyError, TypeError):
    print "JSON format error"