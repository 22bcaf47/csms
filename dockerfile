FROM php:8.1-apache

# Install extensions
RUN docker-php-ext-install mysqli

# Copy code to Apache web root
COPY . /var/www/html/

EXPOSE 80