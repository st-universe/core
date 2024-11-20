<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColonyFieldtype extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_colony_fieldtype.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (1, 101, \'Wiese\', 101, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (4, 111, \'Wald\', 111, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (7, 112, \'Nadelwald\', 112, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (17, 201, \'Wasser\', 201, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (43, 401, \'Wüste\', 401, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (51, 501, \'Eis\', 501, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (63, 701, \'Berge\', 701, 1);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (114, 801, \'Untergrund\', 801, 3);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (115, 802, \'Untergrund-Fels\', 802, 3);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (121, 851, \'Tiefsee\', 851, 3);
INSERT INTO stu_colony_fieldtype (id, field_id, description, normal_id, category) VALUES (122, 900, \'Weltraum\', 900, 2);
        ');
    }
}
