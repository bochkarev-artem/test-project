test-project
============

1. Run `composer install` from project directory
2. Run `php bin/console doctrine:database:create` to create empty database
3. Run `php bin/console doctrine:migrations:migrate` to create tables and populate database with data
4. Run `php bin/console fos:elastica:populate` to index products and routes to Elastica
5. To run import from xml source, run `php bin/console app:import-products-xml`. To run import from DB source, run `php bin/console app:import-products-db`. All parameters should be defined in parameters.yml
6. After import run `php bin/console fos:elastica:populate` again
