<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserMap extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_map.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
        ');
    }
}
