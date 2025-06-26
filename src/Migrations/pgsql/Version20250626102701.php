<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250626102701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER INDEX database_entry_category_id_idx RENAME TO IDX_4D14EE9A12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn ALTER user_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER race SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_character ALTER user_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_wormhole_restrictions ALTER wormhole_entry_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_wormhole_restrictions ALTER user_id SET NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_character ALTER user_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER INDEX idx_4d14ee9a12469de2 RENAME TO database_entry_category_id_idx
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_kn ALTER user_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_wormhole_restrictions ALTER wormhole_entry_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_wormhole_restrictions ALTER user_id DROP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER race DROP NOT NULL
        SQL);
    }
}
