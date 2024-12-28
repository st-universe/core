<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241228133258_Tholian_Web extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes the tholian web reference.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft DROP CONSTRAINT FK_4BD20E2E73D3801E');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT FK_4BD20E2E73D3801E FOREIGN KEY (holding_web_id) REFERENCES stu_tholian_web (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_spacecraft DROP CONSTRAINT fk_4bd20e2e73d3801e');
        $this->addSql('ALTER TABLE stu_spacecraft ADD CONSTRAINT fk_4bd20e2e73d3801e FOREIGN KEY (holding_web_id) REFERENCES stu_tholian_web (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
