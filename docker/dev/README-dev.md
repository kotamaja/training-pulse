
## Installation du framework symfony
````
composer create-project symfony/skeleton:"8.0.*" symfony

cd symfony

composer require webapp
````

## Mise à jour mineur symfony
````
composer update --dry-run

composer update
````


### Création de la base de test

Dans le container mysql :
````
mysql -u root -p
````

puis 

````
CREATE DATABASE clubapi_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON clubapi_test.* TO 'clubapi'@'%';
FLUSH PRIVILEGES;
````
