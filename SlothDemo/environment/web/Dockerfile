FROM php:5.6-apache

# Configure PHP
RUN docker-php-ext-install pdo_mysql

# Configure Apache modules
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/rewrite.load

# Define virtual hosts
COPY ./web/vhosts/celestial.conf /etc/apache2/sites-available/celestial.conf
RUN ln -s /etc/apache2/sites-available/celestial.conf /etc/apache2/sites-enabled/celestial.conf

WORKDIR /app
