# !/bin/sh

sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
sudo -u www-data php bin/console sass:build
sudo -u www-data php bin/console asset-map:compile
sudo -u www-data php bin/console cache:clear
sudo -u www-data php bin/console cache:pool:clear --all
sudo rm -rf var/cache/*
