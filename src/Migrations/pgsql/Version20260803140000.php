<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260803140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds individual crew rank names for users.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stu_user_crew_rank (user_id INT NOT NULL, rank VARCHAR(255) NOT NULL, name VARCHAR(64) NOT NULL, PRIMARY KEY(user_id, rank))');
        $this->addSql('CREATE INDEX user_crew_rank_user_idx ON stu_user_crew_rank (user_id)');
        $this->addSql('ALTER TABLE stu_user_crew_rank ADD CONSTRAINT FK_USER_CREW_RANK_USER FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stu_user_crew_rank');
    }
}
