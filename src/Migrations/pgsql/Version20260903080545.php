<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903080545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update indices for stu_session_strings table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_6468cb57a76ed395');
        $this->addSql('DROP INDEX session_string_user_idx');
        $this->addSql('CREATE INDEX session_string_user_idx ON stu_session_strings (user_id, sess_string)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX session_string_user_idx');
        $this->addSql('CREATE INDEX idx_6468cb57a76ed395 ON stu_session_strings (user_id)');
        $this->addSql('CREATE INDEX session_string_user_idx ON stu_session_strings (sess_string, user_id)');
    }
}
