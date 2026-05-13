ARG PHP_VERSION=8.4

FROM mlocati/php-extension-installer AS php-extension-installer
FROM composer:2 AS composer_bin

FROM php:${PHP_VERSION}-fpm-alpine AS php-fpm-dev




RUN set -xe \
  && apk add --no-cache zip unzip acl git ca-certificates wget curl bash tzdata

WORKDIR /var/www

COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl zip opcache pdo_mysql xdebug

COPY --from=composer_bin /usr/bin/composer /usr/local/bin/composer

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

COPY docker/dev/php/zzz-custom.ini "$PHP_INI_DIR/conf.d/"


COPY docker/dev/php/bin/ /usr/local/bin/
RUN chmod +x /usr/local/bin/composer-*

COPY docker/dev/php/docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint-dev
RUN chmod +x /usr/local/bin/docker-entrypoint-dev


EXPOSE 9000

ENTRYPOINT ["docker-entrypoint-dev"]
CMD ["php-fpm"]