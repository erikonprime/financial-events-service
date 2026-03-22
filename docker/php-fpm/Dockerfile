FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
        zlib1g-dev \
        libssl-dev \
        g++ \
        git \
        libicu-dev \
        libzip-dev \
        zip \
        libz-dev \
        libmemcached-dev \
        libpq-dev \
        librabbitmq-dev \
        libssh-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
        intl \
        opcache \
        pdo \
        pdo_pgsql \
        zip \
        bcmath \
    && pecl channel-update pecl.php.net \
    && pecl install apcu memcached amqp xdebug \
    && docker-php-ext-enable apcu memcached amqp xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/app
