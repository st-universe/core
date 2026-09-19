<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919200951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add cascade deletions for game reset functionality.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_relation_permissions DROP CONSTRAINT fk_4fb919c33256915b');
        $this->addSql('ALTER TABLE stu_relation_permissions DROP CONSTRAINT fk_4fb919c3e7a1254a');
        $this->addSql('ALTER TABLE stu_relation_permissions ADD CONSTRAINT FK_4FB919C33256915B FOREIGN KEY (relation_id) REFERENCES stu_relations (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE stu_relation_permissions ADD CONSTRAINT FK_4FB919C3E7A1254A FOREIGN KEY (contact_id) REFERENCES stu_contactlist (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_relation_permissions DROP CONSTRAINT FK_4FB919C33256915B');
        $this->addSql('ALTER TABLE stu_relation_permissions DROP CONSTRAINT FK_4FB919C3E7A1254A');
        $this->addSql('ALTER TABLE stu_relation_permissions ADD CONSTRAINT fk_4fb919c33256915b FOREIGN KEY (relation_id) REFERENCES stu_relations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stu_relation_permissions ADD CONSTRAINT fk_4fb919c3e7a1254a FOREIGN KEY (contact_id) REFERENCES stu_contactlist (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
