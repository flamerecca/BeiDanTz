FROM php:7.1-fpm

WORKDIR /code/BeiDanTz
ADD ./ /code/BeiDanTz

#####################################
# Non-Root User:
#####################################

# Add a non-root user to prevent files being created with root permissions on host machine.

RUN groupadd -g 1000 beiDantz && \
    useradd -u 1000 -g beiDantz -m beiDantz && \
    apt-get update -yqq


#####################################
# Crontab
#####################################
#USER root
#
#COPY ./crontab /etc/cron.d
#RUN chmod -R 644 /etc/cron.d

RUN echo deb http://ftp.uk.debian.org/debian jessie-backports main \
    >>/etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y libxml2 \
    libxml2-dev \
    zip \
    libzip-dev \
    openssl \
    libssh-dev \
    libmcrypt-dev \
    git \
    wget \
    sudo \
    python-pip \
    && docker-php-ext-install pdo pdo_mysql mbstring json zip mcrypt soap exif

RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/bin
ENV PATH /root/.composer/vendor/bin:$PATH

EXPOSE 9000

CMD php-fpm
