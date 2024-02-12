FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev pkg-config libssl-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libpq-dev \
    libwebp-dev \
    g++ \
    libicu-dev \
    libxml2-dev \
    git \
    vim \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    zlib1g-dev \
    libmagickwand-dev --no-install-recommends \
    libzip-dev \
    libonig-dev \
    procps \
    locales \
    tesseract-ocr-fas


RUN docker-php-ext-configure intl \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install intl \
    && docker-php-ext-install zip \
    && docker-php-ext-install soap \
    && docker-php-ext-configure gd  \
        --with-freetype  \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install sockets \
    && pecl install apcu \
    && pecl install -o -f redis \
    && pecl install xdebug \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
    && docker-php-ext-enable apcu \
    && docker-php-ext-install pdo \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-install exif\
    && docker-php-ext-enable xdebug

RUN apt-get clean && rm -rf /var/lib/opt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini

RUN echo pt_BR.UTF-8 UTF-8 > /etc/locale.gen && locale-gen

WORKDIR /var/www/html

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN sed -i -e 's/listen.*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.conf
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 www-data


# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html


EXPOSE 9000
CMD ["php-fpm"]
