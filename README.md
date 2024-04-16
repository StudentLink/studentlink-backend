<br/>
<p align="center">
    <a href="https://studentlink.fr" target="_blank">
        <img width="50%" src="https://github.com/StudentLink/.github/blob/main/profile/logo.png" alt="StudentLink logo">
    </a>
</p>
<br/>

# StudentLink - Vitrine & API

## Sommaire

- [Présentation](#présentation)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribuer](#contribuer)
- [Licence](#licence)
- [Contact](#contact)

## Présentation

Cet outil est entièrement développé en Symfony, et se compose de deux parties :

- Le site vitrine du projet
- L'API de l'application

## Installation

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