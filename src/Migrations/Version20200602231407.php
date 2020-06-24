<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200602231407 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46F5299398');
        $this->addSql('DROP INDEX IDX_ED896F46F5299398 ON order_detail');
        $this->addSql('ALTER TABLE order_detail CHANGE `order` found_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46AF366115 FOREIGN KEY (found_order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_ED896F46AF366115 ON order_detail (found_order_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46AF366115');
        $this->addSql('DROP INDEX IDX_ED896F46AF366115 ON order_detail');
        $this->addSql('ALTER TABLE order_detail CHANGE found_order_id `order` INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46F5299398 FOREIGN KEY (`order`) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_ED896F46F5299398 ON order_detail (`order`)');
    }
}
