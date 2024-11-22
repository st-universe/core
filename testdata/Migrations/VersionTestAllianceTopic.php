<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAllianceTopic extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a alliance topic.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_alliance_topics (id, board_id, alliance_id, name, last_post_date, user_id, sticky)
                VALUES (1, 1, 2, \'Ally-Topic A\', 1732214228, 101, 1);
        ');
    }
}
