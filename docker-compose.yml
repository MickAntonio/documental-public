version: '3.8'

services:
  laravel:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: documental-api
    restart: always
    ports:
      - "8085:80"
    volumes:
      - .:/var/www/html
      - /var/www/html/vendor
    environment:
      APP_NAME: "Gestão Documental"
      APP_ENV: local
      APP_DEBUG: "true"
      APP_KEY: "base64:voj1iLlkor+s1cm1LcaQPOI5LKVdWAbmOa0rk1ZNNT4="
      DB_CONNECTION: mysql
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: documental_db
      DB_USERNAME: root
      DB_PASSWORD: ""
    depends_on:
      - db
    command: >
      bash -c "chown -R www-data:www-data storage bootstrap/cache &&
              chmod -R 775 storage bootstrap/cache &&
              apache2-foreground"

  db:
    image: mysql:8.0
    container_name: documental-db
    restart: always
    environment:
      MYSQL_DATABASE: documental_db
      MYSQL_ALLOW_EMPTY_PASSWORD: "yes"
      MYSQL_ROOT_PASSWORD: ""
    ports:
      - "3307:3306"
    volumes:
      - db_data:/var/lib/mysql

  phpmyadmin:
    image: phpmyadmin:latest
    container_name: documental-phpmyadmin
    restart: always
    depends_on:
      - db
    ports:
      - "8086:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: 
      PMA_ARBITRARY: 1

  keycloak:
    image: quay.io/keycloak/keycloak:24.0.5-0
    container_name: documental-keycloak
    restart: always
    environment:
      KC_DB: mysql
      KC_DB_URL_HOST: db
      KC_DB_URL_DATABASE: documental_db
      KC_DB_USERNAME: root
      KC_DB_PASSWORD: ""
      KEYCLOAK_ADMIN: admin
      KEYCLOAK_ADMIN_PASSWORD: admin
      # Enable HTTPS
      KC_HTTPS_CERTIFICATE_FILE: /opt/keycloak/certs/server.crt
      KC_HTTPS_CERTIFICATE_KEY_FILE: /opt/keycloak/certs/server.key
      KC_PROXY: edge
    command:
      - start-dev
    ports:
      - "8087:8080"
    depends_on:
      - db
    volumes:
      - ./keycloak-cert:/opt/keycloak/certs

volumes:
  db_data:
