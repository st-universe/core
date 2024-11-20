<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestResearch extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_research.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_research (id, name, description, sort, rumps_id, database_entries, points, commodity_id, reward_buildplan_id, needed_award, award_id, upper_limit_colony_type, upper_limit_colony_amount) VALUES (1001, \'Basisforschung Föderation\', \'Grundforschung der Föderation um weitere Forschungen zu beginnen\', 1, 0, \'{}\', 0, 1701, NULL, NULL, NULL, 1, 1);
INSERT INTO stu_research (id, name, description, sort, rumps_id, database_entries, points, commodity_id, reward_buildplan_id, needed_award, award_id, upper_limit_colony_type, upper_limit_colony_amount) VALUES (1002, \'Basisforschung Romulanisches Imperium\', \'Grundforschung des Romulanischen Imperiums um weitere Forschungen zu beginnen\', 1, 0, \'{}\', 0, 1701, NULL, NULL, NULL, 1, 1);
        ');
    }
}
