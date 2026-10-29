# 🚀 Projet de Conteneurisation Docker — Application Web Multi-Tiers

Ce dépôt contient l'architecture conteneurisée, sécurisée, optimisée (Alpine) et automatisée (CI/CD GitHub Actions) d'une application Web PHP/MySQL de gestion d'articles, conformément aux bonnes pratiques DevOps et DevSecOps.

---

## 1. Architecture de la solution

L'application repose sur une architecture multi-conteneurs isolée orchestrée via **Docker Compose** :

```text
                     [ Utilisateur (Navigateur) ]
                                  │
                                  ▼ Port Hôte : 8080 (configurable via .env)
┌─────────────────────────────────────────────────────────────────────────┐
│ Réseau Bridge Personnalisé : app-network (Isolation & DNS interne)      │
│                                                                         │
│  ┌───────────────────────────────┐     ┌─────────────────────────────┐  │
│  │ Service : web (articles-web)  │     │ Service : db (articles-db)  │  │
│  │ Base : PHP 8.x + Alpine       │────▶│ Base : MySQL 8 / MariaDB    │  │
│  │ User : non-root (www-data)    │3306 │ User : mysql                │  │
│  │ Hardening : Apache + PHP.ini  │     │ Init : articles.sql         │  │
│  │ Healthcheck : HTTP / port 80  │     │ Healthcheck : mysqladmin    │  │
│  └───────────────────────────────┘     └──────────────┬──────────────┘  │
└───────────────────────────────────────────────────────┼─────────────────┘
                                                        │
                                                        ▼ Montage persistant
                                         ┌─────────────────────────────┐
                                         │ Volume nommé : db-data      │
                                         │ (/var/lib/mysql)            │
                                         └─────────────────────────────┘
```

### Arborescence du dépôt :
```text
projet-final-docker/
├── .github/workflows/
│   └── docker.yml                  # Pipeline CI/CD (Lint, Build, Test, Push Docker Hub)
├── docs/
│   └── architecture.md             # Documentation d'architecture détaillée
├── secrets/
│   ├── db_password.txt.example     # Modèle pour le secret du mot de passe applicatif
│   └── db_root_password.txt.example# Modèle pour le secret du mot de passe root MySQL
├── source/
│   ├── app/
│   │   ├── docker/
│   │   │   ├── apache-hardening.conf
│   │   │   └── php-hardening.ini
│   │   ├── .dockerignore
│   │   ├── Dockerfile              # Image Web optimisée et sécurisée (non-root)
│   │   ├── db-config.php           # Connexion PDO lisant les Docker Secrets
│   │   ├── index.php               # Front-end et affichage des articles
│   │   └── validation.php          # Traitement et validation des formulaires
│   └── db/
│       ├── .dockerignore
│       ├── Dockerfile              # Image Base de données avec auto-initialisation
│       └── articles.sql            # Script SQL d'initialisation de la table articles
├── .env.example                    # Modèle des variables d'environnement
├── .gitignore                      # Exclusion des secrets (.env, secrets/*.txt)
├── docker-compose.yml              # Orchestration multi-conteneurs
└── README.md                       # Documentation principale
```

---

## 2. Prérequis

* **Docker Engine** : `>= 24.0`
* **Docker Compose** : `v2.x` (`docker compose`)
* **Git** : Pour le clonage du dépôt

---

## 3. Configuration et Variables d'environnement

Les fichiers sensibles (`.env`, `secrets/db_password.txt`, `secrets/db_root_password.txt`) sont ignorés par Git via le fichier `.gitignore` pour garantir qu'aucun secret ne fuite sur le dépôt.

Avant de lancer la stack sur une nouvelle machine, initialisez ces fichiers à partir des modèles `.example` :

```bash
# 1. Créer le fichier .env à partir du modèle
cp .env.example .env

# 2. Créer les fichiers de secrets Docker à partir des modèles
cp secrets/db_password.txt.example secrets/db_password.txt
cp secrets/db_root_password.txt.example secrets/db_root_password.txt
```

### Variables d'environnement configurables (`.env`) :
| Variable | Description | Valeur par défaut |
| :--- | :--- | :--- |
| `APP_PORT` | Port d'exposition HTTP sur la machine hôte | `8080` |
| `MYSQL_DATABASE` | Nom de la base de données relationnelle | `articles_db` |
| `MYSQL_USER` | Utilisateur applicatif MySQL | `app_user` |
| `MYSQL_HOST` | Nom du service DNS interne de la base de données | `db` |

---

## 4. Commandes de déploiement

### 4.1 Déploiement complet (Build + Run en arrière-plan)
```bash
docker compose up -d --build
```

### 4.2 Vérification du statut des conteneurs et des Healthchecks
```bash
docker compose ps
```
Une fois les deux conteneurs affichés avec le statut `(healthy)`, l'application est accessible sur :  
**[http://localhost:8080/index.php](http://localhost:8080/index.php)**

### 4.3 Déploiement à partir des images publiées sur Docker Hub
```bash
docker pull dockerorgooner/articles-web:latest
docker pull dockerorgooner/articles-db:latest
docker compose up -d
```

### 4.4 Arrêt et nettoyage
```bash
# Arrêter et supprimer les conteneurs (conserve le volume db-data et les articles)
docker compose down

# Arrêter et supprimer les conteneurs ET les volumes (réinitialise la base)
docker compose down -v
```

---

## 5. Procédures de test (Déploiement, Persistance, Réseau, Sécurité)

### 5.1 Test de persistance des données (Volume `db-data`)
1. Ouvrir `http://localhost:8080/index.php` et publier un article (ex: Titre `"Test"`, Texte `"Test"`, Auteur `"Moi"`).
2. Vérifier que l'article apparaît dans la section **"Liste d'articles"**.
3. Supprimer les conteneurs :
   ```bash
   docker compose down
   ```
4. Relancer les conteneurs :
   ```bash
   docker compose up -d
   ```
5. Rafraîchir `http://localhost:8080/index.php` : l'article est toujours présent grâce au volume nommé `db-data`.

### 5.2 Test de réseau et communication inter-conteneurs (`app-network`)
```bash
# Lister les réseaux Docker et inspecter le réseau bridge personnalisé
docker network ls
docker network inspect projet-final-docker_app-network

# Vérifier la résolution DNS interne et la connectivité vers le service db
docker exec -it articles-web ping -c 3 db
docker exec -it articles-web curl -v http://db:3306
```

### 5.3 Test de sécurité et scan de vulnérabilités (Trivy)
```bash
# Vérifier l'exécution sous un utilisateur non-root
docker exec -it articles-web whoami

# Scanner les vulnérabilités des images optimisées
trivy image dockerorgooner/articles-web:latest
trivy image dockerorgooner/articles-db:latest
```

---

## 6. Pipeline CI/CD & Registre Docker Hub

Le pipeline GitHub Actions (`.github/workflows/docker.yml`) s'exécute automatiquement sur chaque `push` et `pull_request` vers `main` :
* **Validation syntaxique PHP (`php -l`)** sur les scripts de l'application.
* **Build des images Docker** (`articles-web` et `articles-db`).
* **Test fonctionnel automatisé** de déploiement des conteneurs.
* **Publication sur Docker Hub** avec taggage automatique `:latest` et `:<commit-sha>`.

### Dépôts publics Docker Hub :
* **Service Web (12.13 MB compressé) :** [`dockerorgooner/articles-web`](https://hub.docker.com/r/dockerorgooner/articles-web)
* **Service Base de données (232.15 MB compressé) :** [`dockerorgooner/articles-db`](https://hub.docker.com/r/dockerorgooner/articles-db)