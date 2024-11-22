<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestNotes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a note.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_notes (id,user_id,date,title,"text") VALUES (42, 101,1732214228,\'Notiztitel\',\'Blabla!\');');
    }
}
