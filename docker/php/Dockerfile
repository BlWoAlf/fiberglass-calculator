# syntax=docker/dockerfile:1.7

# ─────────────────────────────────────────────────────────────────────────────
# Базовый PHP-слой: общий для dev и prod.
#
# Нативный mesh-analyzer (этап 5) сюда не собирается. Когда дойдёт до него —
# отдельная стадия на rust-образе и COPY бинарника в /usr/local/bin.
# ─────────────────────────────────────────────────────────────────────────────
FROM php:8.3-fpm-bookworm AS base

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/home/app/.composer \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl procps ca-certificates gosu libfcgi-bin \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

# pdo_pgsql/pgsql — БД; amqp+sockets — RabbitMQ; bcmath — денежные расчёты без
# потерь; pcntl — корректный graceful shutdown воркера; gd/zip — PDF-смета.
RUN install-php-extensions \
        pdo_pgsql \
        pgsql \
        amqp \
        sockets \
        bcmath \
        intl \
        zip \
        gd \
        exif \
        pcntl \
        opcache \
        @composer

# Healthcheck для php-fpm: cgi-fcgi стучится в pm.status_path из zz-app.conf.
# Имена переменных — из самого скрипта; при расхождении он молча уходит на
# дефолтный /status, получает 404 и всё равно возвращает 0.
ENV FCGI_CONNECT=127.0.0.1:9000 \
    FCGI_STATUS_PATH=/fpm-status

RUN curl -fsSL -o /usr/local/bin/php-fpm-healthcheck \
        https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/v0.5.0/php-fpm-healthcheck \
    && chmod +x /usr/local/bin/php-fpm-healthcheck

# UID хоста: bind-mount не разъезжается по правам на Linux/WSL
RUN if [ "$(id -u www-data)" != "${UID}" ]; then \
        groupmod -o -g "${GID}" www-data \
        && usermod -o -u "${UID}" -d /home/app www-data; \
    fi \
    && mkdir -p /home/app/.composer/cache /var/www/html \
    && chown -R www-data:www-data /home/app /var/www/html

COPY docker/php/conf.d/ /usr/local/etc/php/conf.d/
COPY docker/php/php-fpm.d/zz-app.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# Контейнер стартует под root, entrypoint чинит права на storage/ и сбрасывает
# привилегии через gosu. Процессы приложения работают под www-data.
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]


# ─────────────────────────────────────────────────────────────────────────────
# 3. dev — xdebug/pcov, код приезжает bind-mount'ом
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS dev

RUN install-php-extensions xdebug pcov
COPY docker/php/conf.d-dev/ /usr/local/etc/php/conf.d/


# ─────────────────────────────────────────────────────────────────────────────
# 4. prod — код внутри образа, зависимости без dev, боевой opcache
# ─────────────────────────────────────────────────────────────────────────────
FROM base AS prod

COPY docker/php/conf.d-prod/ /usr/local/etc/php/conf.d/

# Приложение живёт в backend/, контекст сборки — корень проекта.
COPY --chown=www-data:www-data backend/composer.json backend/composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY --chown=www-data:www-data backend/ .
RUN composer dump-autoload --optimize --classmap-authoritative \
    && php artisan package:discover --ansi || true
