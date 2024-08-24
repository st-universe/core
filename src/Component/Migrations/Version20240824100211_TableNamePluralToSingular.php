<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240824100211_TableNamePluralToSingular extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Renames some tables to be singular.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn_characters RENAME TO stu_kn_character');
        $this->addSql('ALTER TABLE stu_user_characters RENAME TO stu_user_character');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn_character RENAME TO stu_kn_characters');
        $this->addSql('ALTER TABLE stu_user_character RENAME TO stu_user_characters');
    }
}
