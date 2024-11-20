<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserLayer extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_layer.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_layer (user_id, layer_id, map_type) VALUES (101, 2, 1);
INSERT INTO stu_user_layer (user_id, layer_id, map_type) VALUES (102, 2, 1);
        ');
    }
}
