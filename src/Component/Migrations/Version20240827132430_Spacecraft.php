<?php

declare(strict_types=1);

namespace Stu\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240827132430_Spacecraft extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates new spacecraft entity for class table inheritance of Ship and Station.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_construction_progress RENAME ship_id TO station_id;');


        //TODO fill stu_rumps_categories->type with data
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE stu_construction_progress RENAME station_id TO ship_id ;');
    }
}
