FROM php:5.6.37-apache

# install system dependencies
# Update stretch repositories
# RUN sed -i -e 's/deb.debian.org/archive.debian.org/g' \
#            -e 's|security.debian.org|archive.debian.org|g' \
#            -e '/stretch-updates/d' /etc/apt/sources.list

RUN echo "deb [trusted=yes] http://archive.debian.org/debian stretch main non-free contrib" > /etc/apt/sources.list \
  && echo "deb-src [trusted=yes] http://archive.debian.org/debian stretch main non-free contrib" >> /etc/apt/sources.list \
  && echo "deb [trusted=yes] http://archive.debian.org/debian-security stretch/updates main non-free contrib" >> /etc/apt/sources.list

RUN apt update \
  && apt install -y libcurl4-openssl-dev \
  # cleanup apt cache
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

# add extensions
RUN docker-php-ext-install curl \
    && docker-php-ext-enable curl \
    && a2enmod rewrite \
    && service apache2 restart
