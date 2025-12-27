<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20251227122810_Clear_Faulty_Tractor_Reference extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Clears the tractored ship reference, if the corresponding ship system is offline.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE stu_spacecraft s SET tractored_ship_id = NULL WHERE s.tractored_ship_id IS NOT NULL AND EXISTS (SELECT * FROM stu_spacecraft_system ss WHERE ss.spacecraft_id = s.id AND ss.system_type = 14 AND ss.mode < 2)');
    }
}
