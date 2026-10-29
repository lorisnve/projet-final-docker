FROM alpine:3.22

# Apache + PHP 8.2 précompilés : aucune compilation, donc aucun outil de build dans l'image
RUN apk upgrade --no-cache \
 && apk add --no-cache apache2 php82 php82-apache2 php82-mysqli php82-pdo_mysql curl

COPY docker/php-hardening.ini /etc/php82/conf.d/99-hardening.ini
COPY docker/apache-hardening.conf /etc/apache2/conf.d/zz-hardening.conf

# Port non privilégié, DocumentRoot standard, logs vers stdout/stderr (rootfs en lecture seule)
RUN sed -ri \
      -e 's/^Listen 80$/Listen 8080/' \
      -e 's#/var/www/localhost/htdocs#/var/www/html#g' \
      -e 's#^ErrorLog .*#ErrorLog /dev/stderr#' \
      -e 's#^(\s*)CustomLog .*#\1CustomLog /dev/stdout combined#' \
      /etc/apache2/httpd.conf \
 && mkdir -p /var/www/html \
 && chown apache:apache /run/apache2

# Code applicatif : lecture seule pour apache (propriétaire root)
COPY *.php /var/www/html/

EXPOSE 8080

USER apache

CMD ["httpd", "-D", "FOREGROUND"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS http://localhost:8080/ || exit 1