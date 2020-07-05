FROM php:7.4.2-cli

ARG ENVIRONMENT_NAME

COPY ./rootfilesystem/ /

RUN apt-get update && apt-get install vim -y && \
    apt-get install openssl -y && \
    apt-get install libssl-dev -y && \
    apt-get install wget -y && \
    apt-get install git -y && \
    apt-get install procps -y && \
    apt-get install htop -y && \
    apt-get install -y supervisor

# install swoole
RUN cd /tmp && git clone https://github.com/swoole/swoole-src.git && \
    cd swoole-src && \
    git checkout v4.5.2 && \
    phpize  && \
    ./configure  --enable-openssl --enable-http2 && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

# Install PDO and PGSQL Drivers
RUN apt-get install -y libpq-dev libpng-dev

# Install php extensions
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
  && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && docker-php-ext-install json \
  && docker-php-ext-install gd

# install some base extensions
RUN apt-get install -y libzip-dev zip \
 && docker-php-ext-install zip

# Install extra dependencies
RUN apt-get install git -y \
    && apt-get install vim -y \
    && apt-get install curl -y \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN git config --global user.email "savio@savioresende.com.br" \
    && git config --global user.name "MasaDB"

# RUN wget -O /usr/local/bin/dumb-init https://github.com/Yelp/dumb-init/releases/download/v1.2.2/dumb-init_1.2.2_amd64
# RUN chmod +x /usr/local/bin/dumb-init
# RUN apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

RUN chmod +x /entrypoint.sh
RUN chmod +x /usr/local/boot/sample.sh

RUN echo "ENVIRONMENT_NAME $ENVIRONMENT_NAME"
RUN if [ "$ENVIRONMENT_NAME" = "production" ]; then \
    CONFIG_FILE="./config-samples/config.json-production-sample"; \
else \
    CONFIG_FILE="./config-samples/config.json-develop-sample"; \
fi

COPY "$CONFIG_FILE" /var/www/html/config.json

ENTRYPOINT ["/entrypoint.sh"]
# CMD []

WORKDIR "/var/www/html"
