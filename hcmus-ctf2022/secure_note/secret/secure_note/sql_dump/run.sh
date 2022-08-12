#!/bin/bash
mysql -u root -p`printenv MYSQL_ROOT_PASSWORD` < /docker-entrypoint-initdb.d/setup.sql