FROM php:8.2-apache

# Extensions PHP, correctifs de sécurité Debian, puis nettoyage des couches
RUN apt-get update \
 && apt-get upgrade -y --no-install-recommends \
 && apt-get install -y --no-install-recommends iputils-ping \
 && docker-php-ext-install mysqli pdo_mysql \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# php.ini de production + durcissement
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php-hardening.ini $PHP_INI_DIR/conf.d/99-hardening.ini

# Apache : port non privilégié (8080), configuration durcie,
# droits d'écriture limités aux dossiers runtime
COPY docker/apache-hardening.conf /etc/apache2/conf-available/zz-hardening.conf
RUN sed -ri 's/^Listen 80$/Listen 8080/' /etc/apache2/ports.conf \
 && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
 && a2enconf zz-hardening \
 && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2

# Code applicatif : lecture seule pour www-data (propriétaire root)
COPY *.php /var/www/html/

EXPOSE 8080

USER www-data

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS http://localhost:8080/ || exit 1