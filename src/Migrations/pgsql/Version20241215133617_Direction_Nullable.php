<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241215133617_Direction_Nullable extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_flight_sig ALTER from_direction DROP NOT NULL');
        $this->addSql('ALTER TABLE stu_flight_sig ALTER to_direction DROP NOT NULL');
        $this->addSql('UPDATE stu_flight_sig SET from_direction = null WHERE from_direction = 0');
        $this->addSql('UPDATE stu_flight_sig SET to_direction = null WHERE to_direction = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE stu_flight_sig SET from_direction = 0 WHERE from_direction IS null');
        $this->addSql('UPDATE stu_flight_sig SET to_direction = 0 WHERE to_direction IS null');
        $this->addSql('ALTER TABLE stu_flight_sig ALTER from_direction SET NOT NULL');
        $this->addSql('ALTER TABLE stu_flight_sig ALTER to_direction SET NOT NULL');
    }
}
