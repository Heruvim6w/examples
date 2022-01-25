FROM php:7.2.1-fpm-alpine3.7

RUN docker-php-ext-install pdo pdo_mysql
RUN apk add --no-cache curl nodejs-npm bash  freetype libpng libxml2-dev supervisor libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev && \
  docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN apk add --update --no-cache zlib zlib-dev

RUN apk add --update --no-cache libintl icu icu-dev autoconf intltool \
    && docker-php-ext-install intl zip

RUN docker-php-ext-install soap bcmath

RUN npm install gulp -g

#RUN docker-php-ext-install zip
RUN mkdir -p /var/www/
WORKDIR /var/www/

RUN apk add --no-cache nano

