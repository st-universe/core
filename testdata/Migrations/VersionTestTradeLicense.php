<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTradeLicense extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_trade_license.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_trade_license (id, posts_id,user_id,date,expired)
                    VALUES (1,2,101,1728921382,2044473382),
                           (2,2,102,1728921382,2044473382);'
        );
    }
}
