FROM php:7.3-apache

RUN apt update

RUN mkdir -p /db
COPY ./db /db
RUN chown -R root:www-data /db
RUN chmod -R g+w /db

RUN mkdir -p /xml
COPY ./xml /xml
RUN chown -R root:www-data /xml
RUN chmod -R g+w /xml

WORKDIR /var/www/html
RUN chown -R root:www-data /var/www/html
RUN chmod 750 /var/www/html
RUN find . -type f -exec chmod 640 {} \;
RUN find . -type d -exec chmod 750 {} \;

COPY ./src .