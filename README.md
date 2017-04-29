test-project
============

1. Run `composer install` from project directory
2. Run `php bin/console doctrine:database:create` to create empty database
3. Run `php bin/console doctrine:migrations:migrate` to create tables and populate database with data
4. Run `php bin/console fos:elastica:populate` to index products to Elastica
