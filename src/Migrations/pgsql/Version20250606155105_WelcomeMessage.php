<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250606155105_WelcomeMessage extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds Welcome Message to Factions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_factions ADD welcome_message TEXT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {

        $this->addSql(<<<'SQL'
            ALTER TABLE stu_factions DROP welcome_message
        SQL);
    }
}
