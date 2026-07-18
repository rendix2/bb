FROM php:8.4-apache

# update
RUN apt-get update

# SW needed
RUN apt-get install make -y --fix-missing

# PDO
RUN apt-get install libpq-dev -y --fix-missing
RUN docker-php-ext-install -j "$(nproc)" pdo
RUN docker-php-ext-install -j "$(nproc)" pdo_mysql
RUN docker-php-ext-install -j "$(nproc)" pdo_pgsql

# intl
RUN apt-get install libicu-dev -y --fix-missing
RUN docker-php-ext-install -j "$(nproc)" intl

# Opcache
RUN docker-php-ext-configure opcache --enable-opcache
RUN docker-php-ext-install -j "$(nproc)" opcache

# mod rewrite
RUN a2enmod rewrite

# Installation of Composer
RUN apt-get install git zip libzip-dev -y --fix-missing
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Custom php.ini
#COPY ./.docker/web/config/php.ini /usr/local/etc/php/

# install npm and node.js
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /var/www/html