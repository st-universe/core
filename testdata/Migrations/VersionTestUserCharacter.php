<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserCharacter extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a user character.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_character (id, name,description,avatar,former_user_id,user_id)
        VALUES (42, \'John Wick\',\'Full time Killer\',NULL,NULL,101);');
    }
}
