<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250114145656 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Minor modifications.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_skill_enhancement_log ADD crew_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_skill_enhancement_log DROP crew_id');
    }
}
