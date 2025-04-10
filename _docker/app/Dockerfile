FROM php:8.2-fpm

# Встановлення залежностей
RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      zip unzip \
      git \
      libjpeg-dev && \
      docker-php-ext-install pdo_mysql bcmath zip && \
      docker-php-ext-configure gd --with-jpeg && \
      docker-php-ext-install -j$(nproc) gd && \
      pecl install xdebug && \
      docker-php-ext-enable xdebug && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Копіювання налаштувань PHP
COPY ./_docker/app/php.ini /usr/local/etc/php/conf.d/php.ini

# Встановлення Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer \
    --install-dir=/usr/local/bin

# Налаштування робочої директорії
WORKDIR /var/www

# Створення необхідних директорій з правами доступу
RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Відкриття порту та запуск PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]