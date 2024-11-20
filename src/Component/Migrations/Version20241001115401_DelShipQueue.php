<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241001115401_DelShipQueue extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Delete shipqueue entry when ship is deleted';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP CONSTRAINT FK_BEDCCA2FC256317D');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD CONSTRAINT FK_BEDCCA2FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ships (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP CONSTRAINT fk_bedcca2fc256317d');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD CONSTRAINT fk_bedcca2fc256317d FOREIGN KEY (ship_id) REFERENCES stu_ships (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
