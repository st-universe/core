<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114140100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename crew attribute type to rank.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_crew ADD rank VARCHAR(255) DEFAULT NULL');
        $this->addSql(sprintf('UPDATE stu_crew set rank = \'RECRUIT\''));
        $this->addSql('ALTER TABLE stu_crew ALTER rank SET NOT NULL');
        $this->addSql('ALTER TABLE stu_crew DROP type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_crew ADD type SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE stu_crew DROP rank');
    }
}
