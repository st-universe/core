<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestGameConfig extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_game_config.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_game_config (id, option, value) VALUES (1, 1, 1);
        ');
    }
}
