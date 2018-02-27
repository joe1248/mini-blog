# Prerequisite : git clone https://github.com/joe1248/mini-blog.git

# 1. build image
# docker build --tag joseph1248/mini-blog-be:latest --tag joseph1248/mini-blog-be:v1.0 .
# 2. create and run detached container from image
# docker-compose up         Until you skip silently Redis not there (or integrate it...) docker run -d -p80:80 --rm --name blog-be joseph1248/mini-blog-be:latest
# 3. get inside the container
# winpty docker exec -ti blog-be bash
# 4. run tests
# npm run tests

# winpty docker login
# docker push joseph1248/mini-blog-be

#-------------------------------------------------------------------------

FROM php:7.2-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

LABEL maintainer "josephbarban@gmail.com"

# Update OS, add some tools and nodeJS which includes NPM
RUN apt-get update \
    && apt-get install -y \
    vim \
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
	# Redis extension
	&& pecl install -o -f redis \
	# XDebug extension
	&& pecl install xdebug \
    && docker-php-ext-enable \
    xdebug \
	redis \
    && php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer \
	&& chmod +sx /usr/local/bin/composer \
	&& rm -rf /tmp/pear

# Copy composer files
COPY composer.json ./
COPY composer.lock ./

# install Back-End dependencies
RUN composer install --no-interaction --no-scripts --no-autoloader --no-plugins

# copy Server configuration files
    # copy PHP ini file to configure PHP
COPY config/docker/php.ini /usr/local/etc/php/conf.d/
    # copy HTTPD.conf ini file to configure Apache
COPY config/docker/httpd.conf /etc/apache2/sites-enabled/000-default.conf
RUN echo 'ServerName localhost' > /etc/apache2/conf-enabled/AAserverName

# Including apache expires module
RUN ln -s /etc/apache2/mods-available/expires.load /etc/apache2/mods-enabled/

# Enabling module headers
RUN a2enmod headers
# Enabling module rewrite
RUN a2enmod rewrite

# Copy the App
COPY . ./

RUN HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1` \
	&& echo "APACHE USER IS $HTTPDUSER" \
	&& rm -rf var/log \
	&& mkdir var/log \
	&& mkdir var/log/dev \
	&& chown www-data var/log -R \
	&& chmod 777 -R var/log \
	&& rm -rf var/cache \
	&& mkdir var/cache \
	&& mkdir var/cache/dev \
	&& chown www-data var/cache -R \
	&& chmod 777 -R var/cache
	
# generate autoloader MUST BE DONE AFTER COPYING THE APP
RUN composer dump-autoload --optimize

EXPOSE 80 443