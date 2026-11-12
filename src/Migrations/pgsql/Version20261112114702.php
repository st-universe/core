<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261112114702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alte Nanitenproduktion nach der Umstellung auf Komponentenfertigung entfernen';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            (int) $this->connection->fetchOne("SELECT COUNT(*) FROM stu_technology_production WHERE status = 'running' AND (kind = 'nanite_production' OR recipe_id IN (SELECT id FROM stu_technology_recipe WHERE kind = 'nanite_production'))") > 0,
            'Es sind noch offene Nanitenaufträge vorhanden. Zuerst den Refactor der vorherigen Version ausführen'
        );
        $this->addSql("DELETE FROM stu_technology_production WHERE kind = 'nanite_production' OR recipe_id IN (SELECT id FROM stu_technology_recipe WHERE kind = 'nanite_production')");
        $this->addSql("DELETE FROM stu_technology_recipe_cost WHERE recipe_id IN (SELECT id FROM stu_technology_recipe WHERE kind = 'nanite_production')");
        $this->addSql("DELETE FROM stu_technology_recipe WHERE kind = 'nanite_production'");
        $this->addSql('ALTER TABLE stu_technology_production DROP production_tick');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Entfernte Nanitenrezepte und alte Produktionsaufträge können nicht wiederhergestellt werden');
    }
}
