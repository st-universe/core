<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20241008073116_MiningIndices extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Inidices';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER INDEX idx_ac85c1ac64d218e RENAME TO location_id_idx');
        $this->addSql('ALTER INDEX idx_ac85c1acb4acc212 RENAME TO commodity_id_idx');
        $this->addSql('CREATE INDEX ship_id_idx ON stu_mining_queue (ship_id)');
        $this->addSql('ALTER INDEX idx_bbfef8c427d56c25 RENAME TO location_mining_id_idx');
    }

    public function down(Schema $schema): void
    {

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER INDEX commodity_id_idx RENAME TO idx_ac85c1acb4acc212');
        $this->addSql('ALTER INDEX location_id_idx RENAME TO idx_ac85c1ac64d218e');
        $this->addSql('DROP INDEX ship_id_idx');
        $this->addSql('ALTER INDEX location_mining_id_idx RENAME TO idx_bbfef8c427d56c25');
    }
}
