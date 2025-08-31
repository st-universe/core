<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestTradeOffer extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a trade offer.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO stu_trade_offers (id,user_id,posts_id,amount,wg_id,wg_count,gg_id,gg_count,date)
            VALUES (1,101,2,14,50,2,10506,1,1755101209);
       ');
    }
}
