# syntax=docker/dockerfile:1

FROM composer:2 AS composer

FROM php:8.3-apache-bookworm

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    PORT=10000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd intl mbstring mysqli opcache \
    && a2enmod expires headers rewrite setenvif \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --classmap-authoritative \
    --no-interaction \
    --no-progress \
    --prefer-dist

COPY . .

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/apache-ports.conf /etc/apache2/ports.conf
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-groove-production.ini
COPY docker/render-entrypoint.sh /usr/local/bin/groove-render-entrypoint
COPY docker/uploads.htaccess /usr/local/share/groove/uploads.htaccess

RUN chmod 0755 /usr/local/bin/groove-render-entrypoint \
    && install -d -o www-data -g www-data -m 0750 \
        /var/data/uploads/products \
        /var/data/uploads/avatars \
        /var/data/session \
        writable/cache \
        writable/logs \
        writable/uploads \
    && install -o www-data -g www-data -m 0640 \
        /usr/local/share/groove/uploads.htaccess \
        /var/data/uploads/.htaccess \
    && rm -rf public/uploads writable/session \
    && ln -s /var/data/uploads public/uploads \
    && ln -s /var/data/session writable/session \
    && chown -R www-data:www-data writable \
    && composer check-platform-reqs --no-dev \
    && apache2ctl configtest

EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD curl --fail --silent --show-error \
        --header "X-Forwarded-Proto: https" \
        "http://127.0.0.1:${PORT:-10000}/login" > /dev/null || exit 1

ENTRYPOINT ["groove-render-entrypoint"]
CMD ["apache2-foreground"]
