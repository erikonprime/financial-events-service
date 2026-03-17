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
    && docker-php-ext-install \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && pecl channel-update pecl.php.net \
    && pecl install apcu memcached amqp-1.11.0 \
    && docker-php-ext-enable apcu memcached amqp \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
