FROM php:8.3-fpm
RUN apt-get update -y \
  && apt-get install -y --no-install-recommends \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    ssh \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype-dev \
    sudo \
    curl \
    unzip \
    supervisor \
    tofrodos dos2unix \
    && apt-get clean \
  && docker-php-ext-install soap \
  && docker-php-ext-install zip \
  && docker-php-ext-install mysqli \
  && docker-php-ext-install pdo_mysql


RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype && docker-php-ext-install gd


RUN curl -L https://github.com/bcpierce00/unison/releases/download/v2.53.7/unison-2.53.7-ubuntu-x86_64.tar.gz | tar zxv -C /tmp && \
             cd /tmp/bin && \
             cp unison unison-fsmonitor /usr/local/bin && \
             cd /root && rm -rf /tmp/bin

RUN useradd -m -d /home/liquid -s /bin/bash liquid && adduser liquid sudo \
    && echo "liquid ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers \
    && touch /etc/sudoers.d/privacy \
    && echo "Defaults        lecture = never" >> /etc/sudoers.d/privacy \
    && mkdir /home/liquid/liquid && mkdir /var/www/liquid \
    && mkdir /home/liquid/state \
    && rm -r /usr/local/etc/php-fpm.d/*
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
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis

# Install APCu
RUN pecl install apcu \
    && docker-php-ext-enable apcu \
    && pecl clear-


# Install build dependencies and the OPcache extension
RUN docker-php-ext-install opcache

# unison script
ADD conf/unison/liquid.prf /home/liquid/.unison/liquid.prf
ADD conf/unison/unison.sh /usr/local/bin/unison.sh
ADD conf/unison/entrypoint.sh /usr/local/bin/entrypoint.sh
ADD conf/unison/check-unison.sh /usr/local/bin/check-unison.sh
RUN dos2unix /usr/local/bin/unison.sh && dos2unix /usr/local/bin/entrypoint.sh \
    && dos2unix /usr/local/bin/check-unison.sh
RUN chmod +x /usr/local/bin/unison.sh && chmod +x /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/check-unison.sh

# php-fpm config
ADD conf/php-fpm-liquid.conf /usr/local/etc/php-fpm.d/php-fpm-liquid.conf

# supervisord config
ADD conf/supervisord.conf /etc/supervisord.conf

# Copy the opcache.ini into your Docker image
COPY conf/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY conf/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir /windows \
 && cd /windows \
 && curl -L -o unison-windows.zip https://github.com/bcpierce00/unison/releases/download/v2.53.7/unison-2.53.7-windows-x86_64.zip \
 && unzip unison-windows.zip \
 && rm unison-windows.zip \
 && mv 'bin/unison.exe' unison.exe \
 && mv 'bin/unison-fsmonitor.exe' unison-fsmonitor.exe \
# && rm 'unison 2.51.2 GTK.exe'
 && chown -R liquid:liquid .

RUN apt-get clean -y


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

RUN chown -R liquid:liquid /home/liquid && \
    chown -R liquid:liquid /var/www/liquid


# Delete user password to connect with ssh with empty password
RUN passwd liquid -d

EXPOSE 5000
WORKDIR /home/liquid

USER root



ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
