FROM composer:latest as composer
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

LABEL vendor="Phalcon" \
      maintainer="Phalcon Team <team@phalcon.io>" \
      description="The PHP image to test the REST API example concepts"

ENV PHALCON_VERSION="5.4.0" \
    PHP_VERSION="8.2"

# Update
RUN apt update -y && \
    apt install -y \
        apt-utils \
        gettext \
        git \
        libicu-dev \
        libzip-dev \
        libgmp-dev \
        vim \
        sudo \
        wget \
        zip

# PECL Packages
RUN pecl install -o -s redis msgpack xdebug && \
    pecl install phalcon-${PHALCON_VERSION}

# Install PHP extensions
RUN docker-php-ext-install \
      intl \
      gettext \
      gmp \
      bcmath \
      pdo_mysql \
      zip

# Install sqids extension
COPY ./ext/sqids.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/sqids.so
COPY ./ext/chronos.so /usr/local/lib/php/extensions/no-debug-non-zts-20220829/chronos.so

# Install PHP extensions
RUN docker-php-ext-enable \
      intl \
      gmp \
      bcmath \
      opcache \
      msgpack \
      phalcon \
      redis \
      sqids \
      chronos \
      xdebug

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Add user
RUN groupadd -g 1000 phalcon
RUN useradd -u 1000 -ms /bin/bash -g phalcon phalcon

# Add user to sudoers
RUN usermod -aG sudo phalcon
RUN echo "phalcon ALL=(ALL:ALL) NOPASSWD: ALL" >> /etc/sudoers

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Bash script with helper aliases
COPY ./config/.bashrc /root/.bashrc

# Change current user to phalcon
USER phalcon
COPY ./config/.bashrc $HOME/.bashrc

# Expose port 9000 and start php-fpm server
EXPOSE 9000

CMD ["php-fpm"]

