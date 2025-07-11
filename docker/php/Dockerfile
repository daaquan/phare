FROM composer:latest AS composer
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

ENV PHALCON_VERSION="5.9.3" \
    PHP_VERSION="8.2"

# Update
RUN apt-get update -y && \
    apt-get install -y \
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
RUN pecl install -o -s redis msgpack && \
    pecl install phalcon-${PHALCON_VERSION}

# Install PHP extensions
RUN docker-php-ext-install \
      intl \
      gettext \
      gmp \
      bcmath \
      pdo_mysql \
      zip

# Install PHP extensions
RUN docker-php-ext-enable \
      intl \
      gmp \
      bcmath \
      opcache \
      msgpack \
      phalcon \
      redis

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

