<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestAlliances extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a test alliance.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_alliances (id, name, description, homepage, date, faction_id, accept_applications, avatar, rgb_code)
            VALUES (2, \'testally\', \'testallytestallytestallytestallytestallytestallytestally\', \'\', 1731253784, null, false, \'\', \'\');'
        );
    }
}
