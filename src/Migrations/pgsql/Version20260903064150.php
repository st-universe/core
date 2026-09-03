<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903064150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove foreign key constraint from stu_session_strings.user_id to stu_user.id.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_session_strings DROP CONSTRAINT fk_6468cb57a76ed395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_session_strings ADD CONSTRAINT fk_6468cb57a76ed395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
