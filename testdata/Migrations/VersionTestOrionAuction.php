<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestOrionAuction extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds Orion auction tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE stu_orion_auction (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, start INTEGER NOT NULL, "end" INTEGER NOT NULL, completed_at INTEGER DEFAULT NULL, auction_amount INTEGER NOT NULL, winner_name VARCHAR(255) DEFAULT NULL, crew_id INTEGER NOT NULL, wanted_commodity_id INTEGER DEFAULT NULL, winner_id INTEGER DEFAULT NULL, image_position SMALLINT NOT NULL, CONSTRAINT FK_ORION_AUCTION_CREW FOREIGN KEY (crew_id) REFERENCES stu_crew (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_ORION_AUCTION_COMMODITY FOREIGN KEY (wanted_commodity_id) REFERENCES stu_commodity (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_ORION_AUCTION_WINNER FOREIGN KEY (winner_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX orion_auction_crew_idx ON stu_orion_auction (crew_id)');
        $this->addSql('CREATE INDEX orion_auction_commodity_idx ON stu_orion_auction (wanted_commodity_id)');
        $this->addSql('CREATE INDEX orion_auction_winner_idx ON stu_orion_auction (winner_id)');
        $this->addSql('CREATE TABLE stu_orion_auction_bid (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, max_amount INTEGER NOT NULL, auction_id INTEGER NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_ORION_BID_AUCTION FOREIGN KEY (auction_id) REFERENCES stu_orion_auction (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_ORION_BID_USER FOREIGN KEY (user_id) REFERENCES stu_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX orion_auction_bid_auction_idx ON stu_orion_auction_bid (auction_id, max_amount)');
        $this->addSql('CREATE INDEX orion_auction_bid_user_idx ON stu_orion_auction_bid (user_id)');
    }
}
