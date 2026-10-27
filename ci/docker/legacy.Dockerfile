# Test-only runtime for the pinned v1.5.1 source and its original lockfile.
FROM php:8.1-cli
RUN apt-get update && apt-get install -y --no-install-recommends unzip zip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-scripts --no-plugins --prefer-dist --no-interaction \
    && mkdir -p private/cache/database/cache private/cache/database/proxy \
       private/cache/export/log private/cache/sessions private/cache/templates uploads
ENV COMPOSER_ALLOW_SUPERUSER=1
