# PROJET : Conteneurisation d'une application Web multi-tiers avec Docker

**Livrables :** Dépôt Git, rapport technique PDF, images publiées sur Docker Hub, démonstration.

---

## 1. Contexte
Vous êtes développeur full-stack dans une entreprise. L'équipe DevOps vous demande de conteneuriser une application Web existante (fournie) composée de :
* Un front-end et une API backend en PHP
* Une base de données MySQL/MariaDB

L'objectif est de passer d'une architecture monolithique à une architecture multi-conteneurs orchestrée avec Docker Compose, en respectant les bonnes pratiques de sécurité, d'optimisation et d'automatisation (CI/CD).

## 2. Objectifs pédagogiques
* Concevoir une architecture conteneurisée multi-services.
* Écrire des Dockerfiles optimisés et sécurisés.
* Orchestrer plusieurs conteneurs avec Docker Compose.
* Gérer les réseaux et les volumes Docker.
* Appliquer les bonnes pratiques de sécurité (utilisateur non-root, secrets, scan de vulnérabilités).
* Optimiser la taille des images (multi-stage builds, images légères).
* Mettre en place un pipeline CI/CD (GitHub Actions / GitLab CI).
* Publier des images sur Docker Hub.

## 3. Prérequis
* Maîtrise des commandes Linux de base.
* Notions de réseaux (IP, ports, DNS).
* Connaissance de PHP et MySQL.
* Avoir suivi le cours sur la conteneurisation Docker.

---

## 4. Détail des parties

### Partie 1 - Conception de l'architecture
* Analyser l'application fournie (`sources/app` et `sources/db`).
* Proposer une architecture multi-conteneur :
  * **Service Web :** PHP 8.x + Apache (ou Nginx + PHP-FPM)
  * **Service Base de données :** MySQL 8 / MariaDB
  * **(Optionnel) Service d'administration :** phpMyAdmin ou Adminer
* Définir :
  * Les réseaux nécessaires (réseau bridge personnalisé).
  * Les volumes pour la persistance de la base.
  * Les variables d'environnement (fichier `.env`).
* Rédiger un schéma d'architecture (draw.io, papier, etc.).

### Partie 2 - Dockerfiles optimisés et sécurisés

#### 2.1 Dockerfile pour le service Web
* Utiliser une image de base légère : `php:8.2-apache` ou `php:8.2-fpm-alpine`.
* Créer un utilisateur non-root (`www-data` ou autre).
* Copier le code source dans `/var/www/html`.
* Installer les extensions PHP nécessaires (`mysqli`, `pdo_mysql`).
* Configurer Apache (`DocumentRoot`, modules).
* Nettoyer les couches (`apt-get clean`, `rm -rf /var/lib/apt/lists/*`).
* Exposer le port `80`.
* Ajouter un `HEALTHCHECK`.

#### 2.2 Dockerfile pour la base de données
* Utiliser l'image officielle `mysql:8` ou `mariadb`.
* Ajouter un script d'initialisation (`articles.sql`) dans `/docker-entrypoint-initdb.d/`.
* Définir les variables d'environnement pour le mot de passe root et la base.

#### 2.3 Bonnes pratiques
* Créer un fichier `.dockerignore`.
* Ne pas utiliser `chmod 777` (expliquer pourquoi c'est une faille).
* Justifier chaque instruction dans le rapport.

### Partie 3 - Orchestration avec Docker Compose
* Créer un fichier `docker-compose.yml` avec :
  * Les services `web` et `db`.
  * Un réseau personnalisé (`app-network`).
  * Des volumes nommés (`db-data`) pour MySQL.
  * Des variables d'environnement via `.env`.
  * Des healthchecks pour les deux services.
  * Une politique de redémarrage (`restart: unless-stopped`).
* Lancer la stack : `docker compose up -d`.
* Vérifier que l'application fonctionne (accès à `http://localhost:8080`).
* Tester la persistance :
  * Ajouter un article via l'interface.
  * Arrêter et supprimer les conteneurs (`docker compose down`).
  * Relancer (`docker compose up -d`).
  * Vérifier que l'article est toujours présent.
* Expliquer le rôle de chaque section du fichier Compose.

### Partie 4 - Réseaux et communication inter-conteneurs
* Inspecter les réseaux : `docker network ls` et `docker network inspect`.
* Vérifier que les conteneurs communiquent via leurs noms de service (ex: `db`).
* Tester la connectivité :
  * `docker exec -it web ping db`
  * `docker exec -it web curl http://db:3306`
* Expliquer l'isolation réseau et l'intérêt du réseau bridge personnalisé.

### Partie 5 - Sécurité et bonnes pratiques
* **Scan de vulnérabilités :**
  * Installer Trivy ou utiliser Docker Scout.
  * Scanner les images : `trivy image mon-image:latest`.
  * Identifier et corriger les vulnérabilités critiques.
* **Gestion des secrets :**
  * Utiliser des secrets Docker (`docker secret`) ou des variables d'environnement.
  * Ne jamais coder en dur les mots de passe dans les Dockerfiles.
* **Limitation des privilèges :**
  * Utiliser `USER` non-root dans le Dockerfile.
  * Ajouter `read_only: true` ou `cap_drop` dans Compose.
* Documenter les mesures de sécurité prises.

### Partie 6 - Optimisation des images
* Comparer la taille des images avant/après optimisation.
* Utiliser des images de base légères (`alpine`, `slim`).
* Nettoyer les couches inutiles.
* Mettre en place un multi-stage build si pertinent (ex: compilation d'assets).
* Créer un fichier `.dockerignore` efficace.

### Partie 7 - CI/CD et publication
* **Pipeline CI/CD :**
  * Créer un fichier `.github/workflows/docker.yml` (ou `.gitlab-ci.yml`).
  * Le pipeline doit :
    * Se déclencher sur `push` ou `pull_request`.
    * Construire les images Docker.
    * Exécuter des tests simples (ex: `php -l`, tests unitaires).
    * Se connecter à Docker Hub via des secrets.
    * Pousser les images avec un tag `latest` et le SHA du commit.
* **Publication sur Docker Hub :**
  * Créer un dépôt public sur `hub.docker.com`.
  * Se connecter : `docker login`.
  * Tagger et pousser les images : `docker push <user>/<repo>:tag`.
  * Vérifier que les images sont disponibles.

### Partie 8 - Tests, documentation et soutenance
* **`README.md` complet :**
  * Architecture.
  * Prérequis.
  * Commandes de déploiement.
  * Variables d'environnement.
  * Procédure de test.
* **Tests de déploiement :**
  * Cloner le dépôt sur une autre machine.
  * Lancer `docker compose up -d` et vérifier que tout fonctionne.
* **Soutenance :**
  * Présentation de l'architecture.
  * Démonstration en direct.
  * Retour sur les difficultés et les choix techniques.

---

## 6. Évaluation (20 points)

| Critère | Points |
| :--- | :---: |
| Fonctionnement de la stack (démo) | 6 |
| Qualité des Dockerfiles (optimisation, sécurité) | 4 |
| Docker Compose (réseaux, volumes, healthchecks) | 4 |
| Pipeline CI/CD et publication | 3 |
| Rapport technique et documentation | 3 |
| **Total** | **20** |