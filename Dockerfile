FROM php:8.4-fpm as php

RUN apt-get update -y \
    && apt-get install -y unzip libpq-dev libcurl4-gnutls-dev \
    && docker-php-ext-install pdo pdo_mysql bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

WORKDIR /var/www
COPY . .

COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer

COPY Docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PORT=8000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
