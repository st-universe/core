<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserPirateRound extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_pirate_round.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_pirate_round (id, user_id, pirate_round_id, destroyed_ships, prestige)
                VALUES (1, 101, 2, 5, 100),
                       (2, 12, 2, 17, 370);
        ');
    }
}
