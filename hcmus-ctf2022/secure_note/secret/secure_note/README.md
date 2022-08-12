# Setup
1. Delete everything in `secure_note/src/notes`
3. Run `docker-compose down`
2. Run `docker-compose up`
3. Waiting for docker setup to finish, especially MySQL service (If you can see `MySQL init process done. Ready for start up` in the console, then it has done)
4. Run `python3 setup_flag.py` to push flag on site
5. Check `src/notes`, if there is any file then setup has done!