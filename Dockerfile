# Prerequisite : git clone ...

# USING it:
# docker build -t my_image .
# docker run -d -p80:80 --rm --name my_web_server my_image

#-------------------------------------------------------------------------

FROM php:7.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

LABEL maintainer "josephbarban@gmail.com"

# Update OS, add some tools and nodeJS which includes NPM
RUN apt-get update \
    && apt-get install -y \
    apt-utils \
    # net-tools needed to be able to test network interfaces: netstat -tlnp
    net-tools \
    mysql-client \
    zip \
    # libzip-dev needed by docker-php-ext-install zip
    libzip-dev \
    unzip \
    git \
    # wget and gnupg need by node.js
    wget \
    gnupg \
    && curl -sL https://deb.nodesource.com/setup_8.x | bash - \
    && apt-get install -y nodejs

# get PHP extensions and composer
RUN docker-php-ext-install \
    pdo_mysql \
    # zip extension needed by phpunit
    zip \
    && pecl install xdebug \
    && docker-php-ext-enable \
    xdebug \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer

# Copy composer files
COPY composer.json ./
COPY composer.lock ./

# install Back-End dependencies
RUN composer install --no-interaction --no-scripts --no-autoloader --no-plugins

# Copy NPM files
#COPY package.json ./
#COPY package-lock.json ./

# install Front-End dependencies
#RUN npm install --ignore-scripts --unsafe-perm

# BUILD FRONT-end minimized assets
#COPY assets ./assets
#COPY public ./public
#COPY .babelrc ./
#COPY tsconfig.json ./
#COPY webpack.config.js ./
#RUN npm run dev

# copy Server configuration files
    # copy PHP ini file to configure PHP
COPY config/docker/php.ini /usr/local/etc/php/conf.d/
    # copy HTTPD.conf ini file to configure Apache
COPY config/docker/httpd.conf /etc/apache2/sites-enabled/000-default.conf

# Including apache expires module
RUN ln -s /etc/apache2/mods-available/expires.load /etc/apache2/mods-enabled/

# Enabling module headers
RUN a2enmod headers
# Enabling module rewrite
RUN a2enmod rewrite

# Copy the App
COPY . ./

# generate autoloader MUST BE DONE AFTER COPYING THE APP
RUN composer dump-autoload --optimize

# DELETE IN PRODUCTION
RUN echo '<?php phpinfo();' > ./public/info.php

EXPOSE 80 443