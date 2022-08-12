from flask import Flask, jsonify, request
from Crypto.Cipher import AES
from Crypto.Random import get_random_bytes
from Crypto.Util.Padding import pad, unpad
from base64 import b64encode, b64decode

class AESCipher:
    def __init__(self, key):
        self.key = key

    def encrypt(self, pt):
        iv = get_random_bytes(AES.block_size)
        self.cipher = AES.new(self.key, AES.MODE_CBC, iv)
        return iv + self.cipher.encrypt(pad(pt, AES.block_size))

    def decrypt(self, ct):
        self.cipher = AES.new(self.key, AES.MODE_CBC, ct[:AES.block_size])
        return unpad(self.cipher.decrypt(ct[AES.block_size:]), AES.block_size)

# flag1 = open('flag1.txt', 'r').read()
flag2 = open('flag2.txt', 'r').read()
key = bytes.fromhex(open('key.txt', 'r').read())
crypto_service = AESCipher(key)
app = Flask(__name__)

@app.route('/')
def index():
	return "Hello from API"

@app.route('/normal', methods=['GET'])
def normal():
	words = [
		{
			'word': 'milkshake',
			'type': 'noun',
			'pronoun': '/ˈmɪlk.ʃeɪk/',
			'meaning': 'a drink made of milk and usually ice cream and a flavour such as fruit or chocolate, mixed together until it is smooth',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/milksh_noun_002_23336.jpg?version=5.0.243'
		},
		{
			'word': 'lemon',
			'type': 'noun',
			'pronoun': '/ˈlem.ən/',
			'meaning': 'an oval fruit that has a thick, yellow skin and sour juice, or the small tree on which this fruit grows',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/lemon_noun_002_20971.jpg?version=5.0.243'
		},
		{
			'word': 'fruitcake',
			'type': 'noun',
			'pronoun': '/ˈfruːt.keɪk/',
			'meaning': 'a cake containing a lot of dried fruit, such as raisins',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/fruitc_noun_002_15110.jpg?version=5.0.243'
		},
		{
			'word': 'gate',
			'type': 'noun',
			'pronoun': '/ɡeɪt/',
			'meaning': 'a part of a fence or outside wall that is fixed at one side and opens and closes like a door, usually made of metal or wooden strips',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/gate_noun_002_15515.jpg?version=5.0.243'
		},
		{
			'word': 'living room',
			'type': 'noun',
			'pronoun': '/ˈlɪv.ɪŋ ˌruːm/',
			'meaning': 'the room in a house or apartment that is used for relaxing in and entertaining guests',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/living_noun_002_21461.jpg?version=5.0.243'
		},
		{
			'word': 'bulldozer',
			'type': 'noun',
			'pronoun': '/ˈbʊlˌdəʊ.zər/',
			'meaning': 'a heavy vehicle with a large blade in front, used for pushing earth and stones away and for making areas of ground flat at the same time',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/bulldo_noun_002_04858.jpg?version=5.0.243'
		},
		{
			'word': 'scoreboard',
			'type': 'noun',
			'pronoun': '/ˈskɔː.bɔːd/',
			'meaning': 'a large board on which the score of a game is shown',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/scoreb_noun_002_32427.jpg?version=5.0.243'
		},
		{
			'word': 'juggler',
			'type': 'noun',
			'pronoun': '/ˈdʒʌɡ.lər/',
			'meaning': 'a person who juggles objects in order to entertain people',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/juggle_noun_002_19954.jpg?version=5.0.243'
		},
		{
			'word': 'screwdriver',
			'type': 'noun',
			'pronoun': '/ˈskruːˌdraɪ.vər/',
			'meaning': 'a tool for turning screws, consisting of a handle joined to a metal rod shaped at one end to fit in the cut in the top of the screw',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/screwd_noun_002_32519.jpg?version=5.0.243'
		},
		{
			'word': 'photocopier',
			'type': 'noun',
			'pronoun': '/ˈfəʊ.təʊˌkɒp.i.ər/',
			'meaning': 'a machine that makes copies of documents using a photographic process',
			'image': 'https://dictionary.cambridge.org/vi/images/thumb/photoc_noun_002_27467.jpg?version=5.0.243'
		},
	]
	return jsonify(words)

@app.route('/vip/<username>/<key>', methods=['GET'])
def vip(username, key):
	license = crypto_service.decrypt(bytes.fromhex(key))
	try:
		words = [
				{
					'word': 'boomerang',
					'type': 'noun',
					'pronoun': '/ˈbuː.mə.ræŋ/',
					'meaning': 'a curved stick that, when thrown in a particular way, comes back to the person who threw it',
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/boomer_noun_002_04129.jpg?version=5.0.243'
				},
				{
					'word': 'marble',
					'type': 'noun',
					'pronoun': '/ˈmɑː.bəl/',
					'meaning': 'a small ball, usually made of coloured or transparent glass, that is used in children\'s games',
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/marble_noun_002_22382.jpg?version=5.0.243'
				},
				{
					'word': 'motocross',
					'type': 'noun',
					'pronoun': '/ˈməʊ.tə.krɒs/',
					'meaning': 'the sport of racing over rough ground on special motorcycles',
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/motocr_noun_002_24012.jpg?version=5.0.243'
				},
				{
					'word': 'sofa',
					'type': 'noun',
					'pronoun': '/ˈsəʊ.fə/',
					'meaning': 'a long, soft seat with a back and usually arms, on which more than one person can sit at the same time',
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/sofa_noun_002_34673.jpg?version=5.0.243'
				},
				{
					'word': 'shower',
					'type': 'noun',
					'pronoun': '/ʃaʊər/',
					'meaning': 'a device that releases drops of water through a lot of very small holes and that you stand under to wash your whole body',
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/shower_noun_002_33580.jpg?version=5.0.243'
				},
			]
		license = license.decode('utf-8')
		license_infos = license.split('|')
		header = license_infos[0]
		user = license_infos[1]
		issuer = license_infos[2]
		long = license_infos[3]

		require = data.split('|')

		if header == require[0] and user == username and issuer == require[2] and long == require[4]:
			words.append({
					'word': 'flag',
					'type': 'noun',
					'pronoun': '/flæɡ/',
					'meaning': 'flag', #flag1,
					'image': 'https://dictionary.cambridge.org/vi/images/thumb/flag_noun_002_14126.jpg?version=5.0.243'
				})
		return jsonify(words)
	except:
		return "False"

@app.route('/debug', methods=['POST'])
def debug():
	return open('app.py').read()

@app.route('/flag2', methods=['GET'])
def fl2():
	return flag2

if __name__ == '__main__':
	app.run(host="0.0.0.0", port=8080, debug=False)