<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestRumpsRoles extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_rumps_roles.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_rumps_roles (id, name) VALUES (1, \'Phaserschiff\');
INSERT INTO stu_rumps_roles (id, name) VALUES (2, \'Pulswaffenschiff\');
INSERT INTO stu_rumps_roles (id, name) VALUES (3, \'Torpedoschiff\');
INSERT INTO stu_rumps_roles (id, name) VALUES (4, \'Forschungsschiff\');
INSERT INTO stu_rumps_roles (id, name) VALUES (5, \'Kolonieschiff\');
INSERT INTO stu_rumps_roles (id, name) VALUES (6, \'Großraumfrachter\');
INSERT INTO stu_rumps_roles (id, name) VALUES (7, \'Langstreckenfrachter\');
INSERT INTO stu_rumps_roles (id, name) VALUES (8, \'Kurzstreckenfrachter\');
INSERT INTO stu_rumps_roles (id, name) VALUES (9, \'Shuttle\');
INSERT INTO stu_rumps_roles (id, name) VALUES (10, \'Raumkonstrukt\');
INSERT INTO stu_rumps_roles (id, name) VALUES (11, \'Kleines Depot\');
INSERT INTO stu_rumps_roles (id, name) VALUES (12, \'Depot\');
INSERT INTO stu_rumps_roles (id, name) VALUES (13, \'Werft\');
INSERT INTO stu_rumps_roles (id, name) VALUES (14, \'Sensorenphalanx\');
INSERT INTO stu_rumps_roles (id, name) VALUES (15, \'Außenposten\');
INSERT INTO stu_rumps_roles (id, name) VALUES (16, \'Basis\');
INSERT INTO stu_rumps_roles (id, name) VALUES (17, \'Adventstür\');
INSERT INTO stu_rumps_roles (id, name) VALUES (18, \'Energienetz\');
        ');
    }
}
