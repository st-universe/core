<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTradePost extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_trade_posts.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_trade_posts (id, user_id, name ,description,station_id,trade_network, level,transfer_capacity,storage,is_dock_pm_auto_read)
                VALUES (2, 101,\'Goldene Kugel\',\'Its the motherfucking D.R.E\', 43,101,1,200,10000,1);'
        );
    }
}
