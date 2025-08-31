<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestFleet extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_fleets.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_fleets (id, name,user_id,ships_id,defended_colony_id,blocked_colony_id,sort,is_fixed)
                VALUES (77, \'[b][color=#760505]The FLEET[/color][/b]\',101,77,NULL,NULL,29,0),
                        (42, \'[b][color=#760505]Fleet of 42[/color][/b]\',101,42,NULL,NULL,28,0);'
        );
    }
}
