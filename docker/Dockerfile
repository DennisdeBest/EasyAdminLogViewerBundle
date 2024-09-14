#syntax=docker/dockerfile:1.4

FROM dunglas/frankenphp:1.2.1-alpine AS frankenphp_upstream
FROM composer/composer:2.7.6-bin AS composer_upstream

FROM frankenphp_upstream AS frankenphp_base

WORKDIR /app

RUN apk add --no-cache \
		acl \
		file \
		gettext \
		git \
	;

RUN set -eux; \
	install-php-extensions \
		apcu \
		intl \
		opcache \
		gd \
		xsl \
		zip \
    pdo_mysql \
	;

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"
ENV APP_ENV=dev

COPY --from=composer_upstream --link /composer /usr/bin/composer

ARG PHP_CS_FIXER_VERSION=v3.64.0

RUN wget "https://github.com/FriendsOfPHP/PHP-CS-Fixer/releases/download/$PHP_CS_FIXER_VERSION/php-cs-fixer.phar" -O php-cs-fixer && \
    chmod a+x php-cs-fixer && \
    mv php-cs-fixer /usr/local/bin/php-cs-fixer

COPY --link --chmod=755 docker/entrypoint.sh /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
