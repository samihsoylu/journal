FROM php:8.0-apache

# Copy files to /var/www/html as www-data user
ADD --chown=www-data:www-data . /var/www/html/

# Intall php extension and composer with docker-php-extension-installer and enable apache rewrite
RUN curl -sSLf \
    -o /usr/local/bin/install-php-extensions \
    https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions @composer json pdo openssl tokenizer mbstring ctype pcre session pdo_mysql zip && \
    a2enmod rewrite

# install zip package
RUN apt-get update && apt-get install -y \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Change document root to /var/www/html/public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Use php.ini for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Change variables_order to load .env file
RUN echo 'variables_order = "EGPCS"' >> $PHP_INI_DIR/conf.d/user.ini

# Install wait-for-it.sh to wait for db available at start
ADD https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh /usr/local/bin/
RUN chmod 700 /usr/local/bin/wait-for-it.sh 

# Change workdir and user for "composer install"
WORKDIR /var/www/html
USER www-data
RUN composer install --no-dev --no-cache

ADD ./ci/docker/docker-entrypoint.sh /usr/local/bin
EXPOSE 80
USER root
ENTRYPOINT [ "docker-entrypoint.sh" ]
CMD ["apache2-foreground"]
