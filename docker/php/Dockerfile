# infra-10032026-Maurice
# Modification type: bake the legacy Laravel application and deterministic vendor tree into a non-root PHP image.
FROM composer:1.10.27@sha256:dbc308a7af43fd5b1084ced476e67df2a34e04371100fae720f4893321f23c6d AS composer

FROM php:7.4.33-fpm-bullseye@sha256:3ac7c8c74b2b047c7cb273469d74fc0d59b857aa44043e6ea6a0084372811d5b

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql zip

RUN groupadd --gid 1000 app \
    && useradd --uid 1000 --gid 1000 --create-home --shell /usr/sbin/nologin app

WORKDIR /var/www/html

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
COPY . .
RUN mkdir -p database/seeds database/factories
RUN composer install --no-interaction --prefer-dist --no-progress --no-dev --no-plugins --optimize-autoloader

COPY docker/php/entrypoint.sh /usr/local/bin/ojt-php-entrypoint
RUN mkdir -p storage bootstrap/cache \
    && chmod 0755 /usr/local/bin/ojt-php-entrypoint \
    && chown -R app:app /var/www/html

USER app
ENTRYPOINT ["/usr/local/bin/ojt-php-entrypoint"]
CMD ["php-fpm", "-F"]
