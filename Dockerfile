FROM php:8.2-fpm
RUN apt-get update -y \
  && apt-get install -y \
    libxml2-dev \
     libzip-dev \
    zlib1g-dev \
    ssh \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype-dev \
    curl \
  && docker-php-ext-install soap \
  && docker-php-ext-install zip \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install pdo_mysql


RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && docker-php-ext-install gd



#XDebug
#RUN pecl install xdebug
#RUN docker-php-ext-install xdebug
# Install bcmath
RUN docker-php-ext-install bcmath


RUN apt-get install -y autoconf
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Install Redis
RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

# Install APCu
RUN pecl install apcu \
        && docker-php-ext-enable apcu \
        && pecl clear-


# Install build dependencies and the OPcache extension
RUN docker-php-ext-install opcache

# Copy the opcache.ini into your Docker image
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
#RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
#    && architecture=$(case $(uname -m) in i386 | i686 | x86) echo "i386" ;; x86_64 | amd64) echo "amd64" ;; aarch64 | arm64 | armv8) echo "arm64" ;; *) echo "amd64" ;; esac) \
#    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/$architecture/$version \
#    && mkdir -p /tmp/blackfire \
#    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
#    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get ('extension_dir');")/blackfire.so \
#    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
#    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz


#RUN BLACKFIRE_SERVER_ID="c342f517-2462-438c-83b7-c479801bd16f" \
#RUN BLACKFIRE_SERVER_TOKEN="2b634d538bf47c4e2992d0587e143b87f17bcb182528c72b41df533be3aac794" \
#RUN bash -c "$(curl -L https://installer.blackfire.io/installer.sh)"
#RUN sudo blackfire php:install

# Please note that the Blackfire Probe is dependent on the session module.
# If it isn't present in your install, you will need to enable it yourself.


RUN apt-get clean -y
