<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240930084058_Retrofit extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Retrofit ship queue';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD mode INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD ship_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue ADD CONSTRAINT FK_BEDCCA2FC256317D FOREIGN KEY (ship_id) REFERENCES stu_ships (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BEDCCA2FC256317D ON stu_colonies_shipqueue (ship_id)');
        $this->addSql('ALTER INDEX idx_ab6e5ae96a392d53 RENAME TO IDX_B00DC07A6A392D53');
        $this->addSql('ALTER INDEX idx_ab6e5ae91136be75 RENAME TO IDX_B00DC07A1136BE75');
        $this->addSql('ALTER INDEX idx_5f64a41aa76ed395 RENAME TO IDX_6E46626CA76ED395');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP CONSTRAINT FK_BEDCCA2FC256317D');
        $this->addSql('DROP INDEX UNIQ_BEDCCA2FC256317D');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP mode');
        $this->addSql('ALTER TABLE stu_colonies_shipqueue DROP ship_id');
        $this->addSql('ALTER INDEX idx_b00dc07a6a392d53 RENAME TO idx_ab6e5ae96a392d53');
        $this->addSql('ALTER INDEX idx_b00dc07a1136be75 RENAME TO idx_ab6e5ae91136be75');
        $this->addSql('ALTER INDEX idx_6e46626ca76ed395 RENAME TO idx_5f64a41aa76ed395');
    }
}
