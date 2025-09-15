FROM php:8.0.30-apache-bullseye

RUN apt update && apt install -y default-mysql-client && apt clean

RUN docker-php-ext-install mysqli

RUN a2enmod rewrite

ENTRYPOINT bash ./docker/entrypoint.sh