FROM php:cli

RUN apt update
RUN apt install -y ant git unzip

RUN pecl install xdebug

RUN useradd -ms /bin/bash user

USER user
