<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250514115823_Reset_fixes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Some Schema modifications to fix issues with the reset command';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_alliance_settings DROP CONSTRAINT FK_39FF05F710A0EA3F');
        $this->addSql('ALTER TABLE stu_alliance_settings ADD CONSTRAINT FK_39FF05F710A0EA3F FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE stu_wormhole_restrictions DROP CONSTRAINT FK_76C7B8E0A76ED395');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ADD CONSTRAINT FK_76C7B8E0A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_alliance_settings DROP CONSTRAINT fk_39ff05f710a0ea3f');
        $this->addSql('ALTER TABLE stu_alliance_settings ADD CONSTRAINT fk_39ff05f710a0ea3f FOREIGN KEY (alliance_id) REFERENCES stu_alliances (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE stu_wormhole_restrictions DROP CONSTRAINT fk_76c7b8e0a76ed395');
        $this->addSql('ALTER TABLE stu_wormhole_restrictions ADD CONSTRAINT fk_76c7b8e0a76ed395 FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
