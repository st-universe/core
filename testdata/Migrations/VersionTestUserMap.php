<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserMap extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the user map entries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_map (user_id,layer_id,cx,cy,map_id)
         VALUES (101,2,5,6,15005),
                (101,2,6,6,15006),
                (101,2,7,6,15007),
                (101,2,5,7,15125),
                (101,2,6,7,15126),
                (101,2,7,7,15127),
                (101,2,5,8,15245),
                (101,2,6,8,15246),
                (101,2,7,8,15247);
        ');
    }
}
