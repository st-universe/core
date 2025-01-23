<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestGameTurns extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_game_turns.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_game_turns (id, turn, startdate, enddate, pirate_fleets) VALUES (10415, 1, 1731247445, 0, 0);
        ');
    }
}
