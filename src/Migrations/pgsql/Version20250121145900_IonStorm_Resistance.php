<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250121145900_IonStorm_Resistance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add module specials on tritanium hulls.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_modules_specials (module_id, special_id)
                    VALUES (10122, 17),
                        (10121, 17),
                        (10123, 17),
                        (10124, 17),
                        (10125, 17),
                        (10126, 17),
                        (11124, 17),
                        (11123, 17),
                        (11125, 17),
                        (11126, 17),
                        (11122, 17),
                        (11121, 17);
        ');
    }
}
