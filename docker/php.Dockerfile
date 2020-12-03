FROM phpswoole/swoole:4.5.9-php7.4

# Installation dependencies and PHP core extensions
RUN apt-get update \
        && apt-get -y install --no-install-recommends --assume-yes \
        libpng-dev \
        libzip-dev \
        libzip4 \
        zip \
        unzip \
        git \
        net-tools \
        iputils-ping \
        vim \
        supervisor \
        sudo \
        curl \
        dirmngr \
        apt-transport-https \
        lsb-release \
        ca-certificates \
        libjpeg-dev \
        libfreetype6-dev \
        inotify-tools \
        && curl -sL https://deb.nodesource.com/setup_12.x | sudo -E bash - \
        && apt-get -y install nodejs \
        && rm -r /var/lib/apt/lists/* \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
        && docker-php-ext-install pdo_mysql gd pcntl zip

# Set the WORKDIR to /var/www so all following commands run in /var/www
WORKDIR /var/www
