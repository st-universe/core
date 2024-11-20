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
        $this->addSql('INSERT INTO stu_rumps_roles (id, name)
                VALUES (1, \'Phaserschiff\'),
                       (2, \'Pulswaffenschiff\'),
                       (3, \'Torpedoschiff\'),
                       (4, \'Forschungsschiff\'),
                       (5, \'Kolonieschiff\'),
                       (6, \'Großraumfrachter\'),
                       (7, \'Langstreckenfrachter\'),
                       (8, \'Kurzstreckenfrachter\'),
                       (9, \'Shuttle\'),
                       (10, \'Raumkonstrukt\'),
                       (11, \'Kleines Depot\'),
                       (12, \'Depot\'),
                       (13, \'Werft\'),
                       (14, \'Sensorenphalanx\'),
                       (15, \'Außenposten\'),
                       (16, \'Basis\'),
                       (17, \'Adventstür\'),
                       (18, \'Energienetz\');
        ');
    }
}
