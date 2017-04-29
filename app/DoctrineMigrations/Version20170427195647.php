<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170427195647 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO manufacturer (manufacturer_id, title) VALUES (DEFAULT, 'TOYO')");
        $this->addSql("INSERT INTO product (product_id, external_id, manufacturer_id, title, description, price, image, availability) VALUES (DEFAULT, 39676, (SELECT manufacturer_id FROM manufacturer as m WHERE m.title = 'TOYO'), 'KUMHO 857 205/65R16C 107T', NULL, 59.99, 'http://media2.tyre24.de/images/tyre/857-R-w300-h300-br1.jpg', 32)");
        $this->addSql("INSERT INTO product (product_id, external_id, manufacturer_id, title, description, price, image, availability) VALUES (DEFAULT, 196835, (SELECT manufacturer_id FROM manufacturer as m WHERE m.title = 'TOYO'), 'MARSHAL 857 235/65 R16 115R', 'DOT 2008', 87.40, 'http://media2.tyre24.de/images/tyre/857-R-w300-h300-br1.jpg', 1)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM product WHERE external_id = 39676");
        $this->addSql("DELETE FROM product WHERE external_id = 196835");
        $this->addSql("DELETE FROM manufacturer WHERE title = 'TOYO'");
    }
}
