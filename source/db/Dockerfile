FROM mysql:8.4

# Script exécuté automatiquement au premier démarrage, dans la base MYSQL_DATABASE
COPY articles.sql /docker-entrypoint-initdb.d/01-articles.sql

# mysql-shell (Python vulnérable) inutile au serveur ; gosu inutile si on démarre déjà en mysql
RUN microdnf remove -y mysql-shell \
 && microdnf clean all \
 && rm -f /usr/local/bin/gosu

USER mysql