<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210702075642 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
//       $this->addSql(
//           'CREATE TABLE activity(
//            blocker varchar(255),
//            blockee varchar(255),
//            status int(1),
//            CONSTRAINT fk_license,
//            FOREIGN KEY (blocker, blockee),
//            REFERENCES license_plate(license_plate)'
//       );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
