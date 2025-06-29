<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250628231439_KnArchivFix extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fixes the unique constraint on kn archive former_id to include version, and changes titel and username to TEXT type';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX unique_kn_archiv_former_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ALTER titel TYPE TEXT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ALTER username TYPE TEXT
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_kn_archiv_former_id_version ON stu_kn_archiv (former_id, version)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX unique_kn_archiv_former_id_version
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ALTER titel TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn_archiv ALTER username TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_kn_archiv_former_id ON stu_kn_archiv (former_id)
        SQL);
    }
}
