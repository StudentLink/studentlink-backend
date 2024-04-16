<br/>
<p align="center">
    <a href="https://studentlink.fr" target="_blank">
        <img width="50%" src="https://github.com/StudentLink/.github/blob/main/profile/logo.png" alt="StudentLink logo">
    </a>
</p>
<br/>

# StudentLink - Vitrine & API

## Sommaire

- [I. Présentation](#i-présentation)
- [II. Installation](#ii-installation)
    - [1. Prérequis](#1-prérequis)
    - [2. Cloner le projet](#2-cloner-le-projet)
    - [3. Variables d'environnement](#3-variables-denvironnement)
    - [4. Lancement du Docker](#4-lancement-du-docker)
    - [5. Installation des dépendances](#5-installation-des-dépendances)
    - [6. Création de la base de données](#6-création-de-la-base-de-données)
    - [7. Build des assets](#7-build-des-assets)
    - [8. Accès au projet](#8-accès-au-projet)
- [III. Utilisation](#iii-utilisation)
    - [1. Vitrine](#1-vitrine)
    - [2. API](#2-api)


## I. Présentation

Cet outil est entièrement développé en Symfony, et se compose de deux parties :

- Le site vitrine du projet
- L'API de l'application

## II. Installation

### 1. Prérequis

Pour installer ce projet, vous aurez besoin de :

 - Git
 - Docker
 - Docker Compose

### 2. Cloner le projet

Pour télécharger le projet, il vous suffit de cloner le dépôt et vous rendre dans le dossier correspondant :

```bash
git clone git@github.com:StudentLink/studentlink-backend.git
cd studentlink-backend
```

### 3. Variables d'environnement

Il faut maintenant une certaine liste de variables d'environnement à avoir afin que le projet puisse fonctionner correctement. Vous pouvez faire une copie du fichier `.env.example` et le renommer en `.env.local`.

> Les variables relatives aux bases de données sont aussi à modifier dans le fichier `docker-compose.yml`.

### 4. Lancement du Docker

Si vous utilisez Docker, vous pouvez build et lancer les containers relatifs au projet :

```bash
docker-compose up -d --build
```

### 5. Installation des dépendances

Désormais, vous pouvez vous rendre dans le shell de votre container `projet` et installer les dépendances :

```bash
# Depuis un terminal sur votre machine
docker exec -it projet bash

# Depuis le shell du container
composer install
```

### 6. Création de la base de données

Toujours depuis le shell du container :

```bash
php bin/console doctrine:migrations:migrate
```

**Pour des environnements de dev uniquement :** Si vous souhaitez ajouter des données de test :

```bash
php bin/console doctrine:fixtures:load
```

### 7. Build des assets

Toujours depuis le shell du container :

```bash
# Pour build les fichiers une seule fois
php bin/console sass:build

# Pour build les fichiers en continu
php bin/console sass:build --watch
```

### 8. Accès au projet

Vous pouvez désormais accéder au projet via l'adresse `http://localhost:8741`.

III. Utilisation

### 1. Vitrine

Le site vitrine est accessible à la racine du site web. Différentes routes vitrines sont disponibles.

### 2. API

L'API est accessible via la route `/api`. Différentes routes sont disponibles pour accéder aux différentes ressources.

**Connexion :** `POST /api/login` -> Retourne un token JWT

```json
{
    "email": "example@example.com",
    "password": "password"
}
```

**Inscription :** `POST /api/register` -> Crée un utilisateur et retourne un token JWT

```json
{
    "role": "ROLE_EXAMPLE",
    "displayname": "User EXAMPLE",
    "username": "username",
    "email": "example@example.com", 
    "password": "password",
}
```