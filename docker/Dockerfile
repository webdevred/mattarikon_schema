FROM php:8.0.30-apache-bullseye as base

RUN apt update && apt install -y default-mysql-client && apt clean

RUN docker-php-ext-install mysqli

RUN a2enmod autoindex rewrite

RUN a2disconf docker-php.conf && a2dissite 000-default

FROM base

COPY ./docker/virtualhost.conf /etc/apache2/sites-available/mattarikon.conf

RUN a2ensite mattarikon

ENTRYPOINT bash ./docker/entrypoint.sh
