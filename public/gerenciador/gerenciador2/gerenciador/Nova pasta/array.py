
import json
import requests



def main():

    # Request
    r = requests.get('https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx')




a = '  {
        "id":"",
		"sport_id":"",
		"time":"",
		"time_status:"",
        }'

# python objeto appended
b = {"league":}

c = {"home":,
     "id":"",
	 "name":"",
	}

d = {"our_event_id":}


# parsing JSON string:
z = json.loads(d)

# appending the data
z.update(y)

# the result is a JSON string:
print(json.dumps(z))



if __name__ == '__main__':
    main() 
