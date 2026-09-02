<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Exclude last action from user table and create new table for it.
 */
final class Version20260902074021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Exclude last action from user table and create new table for it.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stu_user_last_action (timestamp INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY (user_id))');
        $this->addSql('ALTER TABLE stu_user_last_action ADD CONSTRAINT FK_D77497D0A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql(<<<'SQL'
            INSERT INTO stu_user_last_action
            (user_id, timestamp)
            SELECT u.id, u.lastaction
            FROM stu_user u
            ORDER BY u.id ASC
        SQL);
        $this->addSql('ALTER TABLE stu_user DROP lastaction');
        $this->addSql('ALTER TABLE stu_session_strings DROP CONSTRAINT fk_6468cb57a76ed395');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_user ADD lastaction INT DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE stu_user u
            SET lastaction = (
                SELECT timestamp
                FROM stu_user_last_action
                WHERE user_id = u.id
            )
        SQL);
        $this->addSql('ALTER TABLE stu_user ALTER lastaction SET NOT NULL');

        $this->addSql('ALTER TABLE stu_user_last_action DROP CONSTRAINT FK_D77497D0A76ED395');
        $this->addSql('DROP TABLE stu_user_last_action');
        $this->addSql('ALTER TABLE stu_session_strings ADD CONSTRAINT fk_6468cb57a76ed395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON UPDATE RESTRICT ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
