<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Override;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240824100211_TableNamePluralToSingular extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Renames some tables to be singular.';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn_characters RENAME TO stu_kn_character');
        $this->addSql('ALTER TABLE stu_user_characters RENAME TO stu_user_character');
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_kn_character RENAME TO stu_kn_characters');
        $this->addSql('ALTER TABLE stu_user_character RENAME TO stu_user_characters');
    }
}
