FROM php:cli

RUN apt update
RUN apt install -y ant git unzip

RUN useradd -ms /bin/bash user

USER user
