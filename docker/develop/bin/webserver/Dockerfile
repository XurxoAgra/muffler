ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-apache-trixie

# Surpresses debconf complaints of trying to install apt packages interactively
# https://github.com/moby/moby/issues/4032#issuecomment-192327844
ARG DEBIAN_FRONTEND=noninteractive

# Update
RUN apt-get -y update --fix-missing && \
    apt-get upgrade -y && \
    apt-get --no-install-recommends install -y apt-utils && \
    rm -rf /var/lib/apt/lists/*


# Install useful tools and install important libaries
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install nano wget dialog libsqlite3-dev libsqlite3-0 && \
    apt-get -y --no-install-recommends install postgresql-client libpq-dev zlib1g-dev libzip-dev libicu-dev && \
    apt-get -y --no-install-recommends install --fix-missing apt-utils build-essential git curl && \
    apt-get -y --no-install-recommends install --fix-missing libcurl4 libcurl4-openssl-dev zip openssl libssl-dev && \
    apt-get -y --no-install-recommends install --fix-missing libonig-dev openssh-client && \
    rm -rf /var/lib/apt/lists/* && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Languajes
RUN apt-get -y update && \
    apt-get install -y locales locales-all
ENV LC_ALL es_ES.UTF-8
ENV LANG es_ES.UTF-8
ENV LANGUAGE es_ES.UTF-8

# Install amqp
RUN apt-get update -y \
    && apt-get -y install librabbitmq-dev \
    && mkdir -p /usr/src/php/ext/amqp \
    && curl -fsSL https://pecl.php.net/get/amqp | tar xvz -C "/usr/src/php/ext/amqp" --strip 1 \
    && docker-php-ext-install amqp

# Other PHP Extensions
RUN docker-php-ext-install pdo && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install pgsql && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install zip && \
    docker-php-ext-install -j$(nproc) intl && \
    docker-php-ext-install gettext && \
    docker-php-ext-install exif && \
    docker-php-ext-install bcmath  && \
    docker-php-ext-install sockets && \
    docker-php-ext-install sysvsem

RUN docker-php-ext-install opcache


# Install Freetype
RUN apt-get -y update && \
    apt-get --no-install-recommends install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd


# Change usermod
ARG USER_ID=1001
ARG GROUP_ID=1001
RUN usermod -u ${USER_ID} www-data
RUN groupmod -g ${GROUP_ID} www-data
RUN usermod -d /home/www-data www-data
RUN mkdir /home/www-data

# Add Autocompletions
ENV SHELL bash
RUN composer global require bamarni/symfony-console-autocomplete
RUN echo '\n\
        \n# Add General Autocompletions\
        \nsource "/etc/profile.d/bash_completion.sh"\
    ' >> /root/.bashrc

# Add vendor/bin to path
ENV SHELL bash
RUN composer global require bamarni/symfony-console-autocomplete
RUN echo '\n\
        \n# Add vendor/bin to PATH\
        \nif [ -d "$HOME/.composer/vendor/bin" ] ; then\
        \n    PATH="$PATH:$HOME/.composer/vendor/bin"\
        \n    eval "$(symfony-autocomplete)"\
        \nfi\
    ' >> /root/.bashrc

# Add Aliases
ADD .bash_aliases /root/.bash_aliases
RUN echo '\n\
        \n# Alias\
        \nif [ -f ~/.bash_aliases ]; then\
        \n    . ~/.bash_aliases\
        \nfi\
    ' >> /root/.bashrc


# Install bash completion
RUN apt-get -y update && \
    apt-get install -y bash-completion

# Change usermod
RUN usermod -u 1001 www-data -d /var/www/html

# Enable apache modules
RUN a2enmod rewrite headers

# Add Autocompletions
ENV SHELL bash
RUN echo '\n\
        \n# Add General Autocompletions\
        \nsource "/etc/profile.d/bash_completion.sh"\
    ' >> /root/.bashrc

# Cleanup
RUN rm -rf /usr/src/*

ADD entrypoint.sh /root/entrypoint.sh

RUN chmod +x /root/entrypoint.sh

CMD ["/root/entrypoint.sh", "apache2-foreground"]
