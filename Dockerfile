FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libcurl4-openssl-dev \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl exif gd intl mbstring mysqli opcache pdo_mysql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/99-ouvidoria.ini
COPY app/ /var/www/html/
COPY docker/entrypoint.sh /usr/local/bin/ouvidoria-entrypoint

RUN chmod +x /usr/local/bin/ouvidoria-entrypoint \
    && mkdir -p /var/www/html/anexos /var/www/html/cache2 \
    && chown -R www-data:www-data /var/www/html/anexos /var/www/html/cache2

ENTRYPOINT ["ouvidoria-entrypoint"]
CMD ["apache2-foreground"]
