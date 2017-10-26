FROM php:cli

RUN apt update
RUN apt install -y ant git unzip

RUN pecl install xdebug

ADD ./php.ini /usr/local/etc/php/

RUN useradd -ms /bin/bash user

USER user
