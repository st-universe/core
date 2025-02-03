<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250203092918 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renaming some indices.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER INDEX ship_crew_spacecraft_idx RENAME TO IDX_4793ED241C6AF6FD');
        $this->addSql('ALTER INDEX ship_crew_colony_idx RENAME TO IDX_4793ED2496ADBADE');
        $this->addSql('ALTER INDEX ship_crew_tradepost_idx RENAME TO IDX_4793ED248B935ABD');
        $this->addSql('ALTER INDEX ship_crew_user_idx RENAME TO IDX_4793ED24A76ED395');
        $this->addSql('ALTER INDEX ship_crew_crew_idx RENAME TO crew_assign_crew_idx');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_4793ed24a76ed395 RENAME TO ship_crew_user_idx');
        $this->addSql('ALTER INDEX idx_4793ed248b935abd RENAME TO ship_crew_tradepost_idx');
        $this->addSql('ALTER INDEX idx_4793ed241c6af6fd RENAME TO ship_crew_spacecraft_idx');
        $this->addSql('ALTER INDEX crew_assign_crew_idx RENAME TO ship_crew_crew_idx');
        $this->addSql('ALTER INDEX idx_4793ed2496adbade RENAME TO ship_crew_colony_idx');
    }
}
