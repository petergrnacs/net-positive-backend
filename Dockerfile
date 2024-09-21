FROM php:8.3-apache

WORKDIR /var/www

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
COPY . .

RUN composer install
RUN a2enmod rewrite

COPY ./public /var/www/html

# A kód dev módban van, és a localhost:16108 portot keresi a backend vonatkozásában.
# docker build . -t net_positive/backend --no-cache
# docker run -d -p 16108:80 net_positive/backend --no-cache 
EXPOSE 80 