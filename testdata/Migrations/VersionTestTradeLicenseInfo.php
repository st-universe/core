<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTradeLicenseInfo extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_trade_license_info.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_trade_license_info (id, posts_id,commodity_id,amount,days,date)
                    VALUES (1, 2,8,15,60, 1720346634);');
    }
}
