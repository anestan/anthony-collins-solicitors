FROM php:8.0-apache

RUN a2enmod rewrite headers
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY ./vhost.conf /etc/apache2/sites-available/000-default.conf