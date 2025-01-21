<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250115122844_Anomaly_Children extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce anomaly parent-children references.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE stu_anomaly ADD CONSTRAINT FK_A1426D11727ACA70 FOREIGN KEY (parent_id) REFERENCES stu_anomaly (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A1426D11727ACA70 ON stu_anomaly (parent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_anomaly DROP CONSTRAINT FK_A1426D11727ACA70');
        $this->addSql('DROP INDEX IDX_A1426D11727ACA70');
        $this->addSql('ALTER TABLE stu_anomaly DROP parent_id');
    }
}
