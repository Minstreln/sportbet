import json

def main():


 import requests


    # Request
 r = requests.get('https://api.b365api.com/v1/betfair/sb/inplay?sport_id=1&token=78614-HWTKKepUL8Ufpx')


def stream_read_json(fn):
    import json
    start_pos = 0
    with open(fn, 'r') as f:
        while True:
            try:
                obj = json.load(f)
                yield obj
                return
            except json.JSONDecodeError as e:
                f.seek(start_pos)
                json_str = f.read(e.pos)
                obj = json.loads(json_str)
                start_pos += e.pos
                yield obj
				
of = open("id","sport_id") 
of.write("""[ "" ] { " " :  }  {
} 

  { "id" : [ "" , "" ,
 "" ] }
""")
of.close()

f = open("json","r")
for r in streamingiterload(f.readlines()):
  print r
f.close()		
		
if __name__ == '__main__':
    main()		
				