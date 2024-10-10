<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241010152457_UniqueBuildplanSignatures extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates unique constraint for buildplan signatures.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX buildplan_signatures_idx ON stu_buildplans (user_id, rump_id, signature)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX buildplan_signatures_idx');
    }
}
