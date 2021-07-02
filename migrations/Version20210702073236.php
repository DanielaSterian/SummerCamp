<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210702073236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity');
        $this->addSql('CREATE UNIQUE INDEX unique_idx ON license_plate (license_plate, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity (id INT AUTO_INCREMENT NOT NULL, blocker INT DEFAULT NULL, blockee INT DEFAULT NULL, status INT DEFAULT NULL, INDEX blocker (blocker), INDEX blockee (blockee), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT activity_ibfk_1 FOREIGN KEY (blocker) REFERENCES license_plate (id)');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT activity_ibfk_2 FOREIGN KEY (blockee) REFERENCES license_plate (id)');
        $this->addSql('DROP INDEX unique_idx ON license_plate');
    }
}
