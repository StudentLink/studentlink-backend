# Symfony Webpack

## Prérequis

- [docker](https://www.docker.com/get-started/)
- [php 8.1.latest](https://www.php.net/downloads.php#v8.1.27)
- [composer](https://getcomposer.org/download/)
- [symfony cli](https://symfony.com/download)

## Installation

```bash
composer install
```

## Démarrage

Lancer le projet en local:
```bash
docker-compose --env-file .env.local up --build -d
```
```bash
symfony serve
```

