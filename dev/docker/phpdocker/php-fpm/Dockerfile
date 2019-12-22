FROM phpdockerio/php74-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
       git \
       subversion \
       php-memcached \
       php7.4-mysql \
       php-redis \
       php7.4-sqlite3 \
       php-xdebug \
       php7.4-bcmath \
       php7.4-bz2 \
       php7.4-gd \
       php-igbinary \
       # php-imagick \
       php7.4-imap \
       php7.4-intl \
       php7.4-soap \
       ssmtp \
       mysql-client \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# WP CLI
RUN echo "installing WP-CLI" \
    && curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp

WORKDIR "/application"
