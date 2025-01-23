<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestLayer extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_layer.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_layer (id, name, width, height, is_hidden, is_finished, is_encoded, award_id)
                VALUES (1, \'Cragganmore Verwerfung\', 120, 120, 1, 1, NULL, NULL),
                       (2, \'Tullamore Trench\', 7, 8, 0, 1, 1, NULL);
        ');
    }
}
