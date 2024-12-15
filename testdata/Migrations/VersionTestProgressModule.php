<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestProgressModule extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_progress_module.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_progress_module (id, progress_id,module_id)
            VALUES (1, 1, 20017);
        ');
    }
}
