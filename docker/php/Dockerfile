# infra-10032026-Maurice
# Modification type: bake the legacy Laravel application and deterministic vendor tree into a non-root PHP image.
FROM php:7.4.33-fpm-bullseye@sha256:3ac7c8c74b2b047c7cb273469d74fc0d59b857aa44043e6ea6a0084372811d5b

# build-10032026-Maurice
# fix-10032026-Maurice: install PHP cURL for bounded verified real-provider probes.
# Install the exact official Composer PHAR over verified HTTPS inside the
# immutable PHP base; no separate mutable Composer image stage is required.
# The legacy Bullseye base has expired live-repository metadata; pin all Debian
# sources to the verified 20221114 snapshot instead of using mutable archives.
RUN printf '%s\n' \
    'deb https://snapshot.debian.org/archive/debian/20221114T000000Z bullseye main' \
    'deb https://snapshot.debian.org/archive/debian-security/20221114T000000Z bullseye-security main' \
    'deb https://snapshot.debian.org/archive/debian/20221114T000000Z bullseye-updates main' \
    > /etc/apt/sources.list \
    && rm -f /etc/apt/sources.list.d/* \
    && apt-get -o Acquire::Check-Valid-Until=false update \
    && apt-get install -y --no-install-recommends ca-certificates curl libcurl4-openssl-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && curl --fail --silent --show-error --location --proto '=https' --tlsv1.2 https://getcomposer.org/download/1.10.27/composer.phar -o /tmp/composer.phar \
    && printf '%s  %s\n' '230d28fb29f3c6c07ab2382390bef313e36de17868b2bd23b2e070554cae23d2' /tmp/composer.phar | sha256sum -c - \
    && install -m 0755 /tmp/composer.phar /usr/local/bin/composer \
    && rm -f /tmp/composer.phar \
    && docker-php-ext-install curl pdo_mysql zip

RUN groupadd --gid 1000 app \
    && useradd --uid 1000 --gid 1000 --create-home --shell /usr/sbin/nologin app

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY . .
RUN mkdir -p database/seeds database/factories
RUN composer install --no-interaction --prefer-dist --no-progress --no-dev --no-plugins --optimize-autoloader

COPY docker/php/entrypoint.sh /usr/local/bin/ojt-php-entrypoint
# feature-10032026-Maurice
# Named local-only operations are baked into the image, not bind-mounted.
COPY docker/php/demo-bootstrap docker/php/demo-reset /usr/local/bin/
# feature-10032026-Maurice
# Test-facing demo operations are direct top-level scripts, not PHP commands.
COPY scripts/demo-inspect scripts/demo-auth-contract scripts/demo-mutate /usr/local/bin/
RUN mkdir -p storage bootstrap/cache \
    && chmod 0755 /usr/local/bin/ojt-php-entrypoint /usr/local/bin/demo-bootstrap /usr/local/bin/demo-reset /usr/local/bin/demo-inspect /usr/local/bin/demo-auth-contract /usr/local/bin/demo-mutate \
    && chown -R app:app /var/www/html

# observability-10042026-Maurice: suppress native FPM notices/access lines so
# application stderr remains a JSONL stream; Laravel errors remain structured.
RUN sed -i 's|^;\?error_log[[:space:]]*=.*|error_log = /dev/null|' /usr/local/etc/php-fpm.conf \
    && printf '%s\n' '[www]' 'access.log = /dev/null' 'catch_workers_output = yes' > /usr/local/etc/php-fpm.d/zz-ojt-logging.conf

USER app
ENTRYPOINT ["/usr/local/bin/ojt-php-entrypoint"]
CMD ["php-fpm", "-F"]
