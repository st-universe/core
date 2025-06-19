<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPirateRound extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_pirate_round.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_pirate_round (id, start, end_time, max_prestige, actual_prestige, faction_winner)
                VALUES (1, 1742651690, 1744898090, 5000, 5000, 3),
                       (2, 1748613290, NULL, 10000, 470, NULL);
        ');
    }
}
