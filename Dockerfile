FROM alpine:edge

RUN apk --update add wget \
		     curl \
		     git \
             yaml-dev \
		     php7-dev \
		     php7-curl \
		     php7-openssl \
		     php7-iconv \
		     php7-json \
		     php7-mbstring \
		     php7-phar \
		     php7-dom --repository http://nl.alpinelinux.org/alpine/edge/testing/ && rm /var/cache/apk/*

RUN apk add --no-cache --virtual .build-deps g++ make autoconf yaml

COPY ./scripts/docker-php-ext-get.sh /usr/bin/docker-php-ext-get.sh
COPY ./scripts/docker-php-ext-configure.sh /usr/bin/docker-php-ext-configure.sh
COPY ./scripts/docker-php-ext-enable.sh /usr/bin/docker-php-ext-enable.sh
COPY ./scripts/docker-php-ext-install.sh /usr/bin/docker-php-ext-install.sh
COPY ./scripts/docker-php-source.sh /usr/bin/docker-php-source.sh

RUN sh /usr/bin/docker-php-source.sh extract

RUN /usr/bin/docker-php-ext-get.sh yaml 2.2.2
RUN /usr/bin/docker-php-ext-install.sh yaml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

RUN mkdir -p /var/www

WORKDIR /var/www

COPY ./src /var/www

CMD ["/bin/sh"]

ENTRYPOINT ["/bin/sh", "-c"]