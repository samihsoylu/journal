FROM php:8.5-apache

# Keep dependency installation cached when application source changes.
COPY --chown=www-data:www-data composer.json composer.lock /var/www/html/

# Intall php extension and composer with docker-php-extension-installer and enable apache rewrite
RUN curl -sSLf \
    -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/download/2.12.3/install-php-extensions && \
    echo '9c26dd516b8147235c4e924c52e2f076fb2b3f278f5457e88918518ebfee9e01  /usr/local/bin/install-php-extensions' | sha256sum -c - && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer json pdo openssl tokenizer mbstring ctype pcre session pdo_mysql zip && \
    a2enmod rewrite

# install zip package
RUN apt-get update && apt-get install -y \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Change document root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Use php.ini for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Change variables_order to load .env file
RUN echo 'variables_order = "EGPCS"' >> $PHP_INI_DIR/conf.d/user.ini

# Install wait-for-it.sh to wait for db available at start
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/81b1373f17855a4dc21156cfe1694c31d7d1792e/wait-for-it.sh /usr/local/bin/
RUN echo 'b7a04f38de1e51e7455ecf63151c8c7e405bd2d45a2d4e16f6419db737a125d6  /usr/local/bin/wait-for-it.sh' | sha256sum -c - && \
    chmod 700 /usr/local/bin/wait-for-it.sh

# Change workdir and user for "composer install"
WORKDIR /var/www/html
RUN mkdir -p \
    private/cache/database/cache \
    private/cache/database/proxy \
    private/cache/export/log \
    private/cache/sessions \
    private/cache/templates \
    uploads && \
    chown www-data:www-data /var/www/html && \
    chown -R www-data:www-data private/cache uploads
USER www-data
RUN composer install --no-dev --no-cache --prefer-dist --no-interaction

COPY --chown=www-data:www-data . /var/www/html/
COPY ./ci/docker/docker-entrypoint.sh /usr/local/bin/
EXPOSE 80
USER root
ENTRYPOINT [ "docker-entrypoint.sh" ]
CMD ["apache2-foreground"]
