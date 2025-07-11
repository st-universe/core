<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestBasicTrade extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_basic_trade.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_basic_trade (id, faction_id,commodity_id,buy_sell,value,uniqid,user_id,date_ms)
                                VALUES (15,1,21,10,56866,\'6750ca719f7dd\',109,1733347953653);
                                    (14,1,4,-10,94010,\'6750c384f36f4\',333,1733346180997),
                                    (13,1,21,-10,56700,\'6750c381789b6\',333,1733346177494),
                                    (12,1,21,-10,56350,\'6750c37f699ed\',333,1733346175433),
                                    (11,1,4,10,94326,\'6750bf17394b7\',113,1733345047235),
                                    (10,1,2,10,192326,\'6750bcef9495b\',107,1733344495609),
                                    (9,1,21,-10,55667,\'67508ca018483\',165,1733332128099),
                                    (8,1,4,-10,94492,\'67508c9b4ca7c\',165,1733332123314),
                                    (7,1,2,-10,191493,\'67508c95d38c1\',165,1733332117867),
                                    (6,1,21,10,54984,\'6750706201daf\',204,1733324898008),
                                    (5,1,21,10,54151,\'67507060631ac\',204,1733324896406),
                                    (4,1,2,10,191143,\'675001da7653a\',120,1733296602485),
                                    (3,1,2,10,190643,\'675001d913499\',120,1733296601079),
                                    (2,1,2,10,190143,\'675001d79271a\',120,1733296599600),
                                    (1,1,2,10,189643,\'674f66c1cc96a\',109,1733256897838);'
        );
    }
}
