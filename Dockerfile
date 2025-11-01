FROM composer:2.7 AS build

WORKDIR /app
COPY . .
RUN composer install --optimize-autoloader --no-interaction --no-progress

FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip \
    apt-get install -y openssl && \
    mkdir -p /opt/keycloak/certs && \
    openssl req -x509 -newkey rsa:2048 -nodes \
      -keyout /opt/keycloak/certs/server.key \
      -out /opt/keycloak/certs/server.crt \
      -days 365 \
      -subj "/CN=localhost"

RUN a2enmod rewrite

# Definir o DocumentRoot para /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=build /app /var/www/html

WORKDIR /var/www/html

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
