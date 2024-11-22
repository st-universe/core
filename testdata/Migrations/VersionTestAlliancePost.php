<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAlliancePost extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a alliance board post.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_alliance_posts (id, topic_id, board_id, name, date, text, user_id, lastedit)
                VALUES (1, 1, 1, \'Ally-Post A\', 1732214228, \'This is a alliance post text.\' , 101, 1732214228);
        ');
    }
}
