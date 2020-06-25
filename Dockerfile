FROM phpswoole/swoole

COPY ./rootfilesystem/ /

RUN apt-get update

# Install PDO and PGSQL Drivers
RUN apt-get install -y libpq-dev libpng-dev

# Install php extensions
RUN docker-php-ext-configure pdo_mysql --with-pdo-mysql=mysqlnd \
  && docker-php-ext-configure mysqli --with-mysqli=mysqlnd \
  && docker-php-ext-install pdo pdo_mysql mysqli \
  && docker-php-ext-install json \
  && docker-php-ext-install gd

#install some base extensions
RUN apt-get install -y libzip-dev zip \
  && docker-php-ext-install zip

# Install debug dependencies
RUN apt-get install git -y \
    && apt-get install vim -y \
    && apt-get install curl -y \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN git config --global user.email "savio@savioresende.com.br" \
	&& git config --global user.name "MasaDB"

RUN chmod +x /entrypoint.sh
RUN chmod +x /usr/local/boot/sample.sh

ENTRYPOINT ["/entrypoint.sh"]
# CMD []

WORKDIR "/var/www/html"
