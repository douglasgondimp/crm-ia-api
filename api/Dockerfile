FROM php:8.4-fpm

# Argumentos vindos do docker-compose.yml
ARG user=laravel
ARG uid=1000

# Dependências de sistema necessárias para as extensões PHP do Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libicu-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    procps \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Extensões PHP usadas pelo Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install \
    pdo_mysql \
    mysqli \
    mbstring \
    bcmath \
    pcntl \
    gd \
    zip \
    intl \
    exif \
    opcache

# Composer
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Usuário não-root para rodar artisan/composer sem problemas de permissão
RUN useradd -G www-data,root -u $uid -d /home/$user $user \
    && mkdir -p /home/$user/.composer \
    && chown -R $user:$user /home/$user

WORKDIR /var/www

USER $user

EXPOSE 9000

CMD ["php-fpm"]
