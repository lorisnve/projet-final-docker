FROM mysql:8.4

# Script exécuté automatiquement au premier démarrage, dans la base MYSQL_DATABASE
COPY articles.sql /docker-entrypoint-initdb.d/01-articles.sql