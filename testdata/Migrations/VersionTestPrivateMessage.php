<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPrivateMessage extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds stu_pms test entities.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_pms (id, send_user,recip_user,"text","date","new",cat_id,href,inbox_pm_id,deleted)
                    VALUES  (1000, 11,102,\'New Pm from user 11 to user 102\',1732134228,1,4785,NULL,NULL,NULL),
                            (999, 12,102,\'Read Pm from user 12 to user 102\',1731714228,0,4785,NULL,NULL,NULL),
                            (998, 13,102,\'New Pm from user 13 to user 102\',1731214228,1,4785,NULL,NULL,NULL),
                            (997, 11,102,\'New Pm 2 from user 11 to user 102\',1731214228,1,4785,NULL,NULL,NULL),
                            (996, 102,14,\'Read Pm from user 102 to user 14 (inbox)\',1731214228,0,323,NULL,NULL,NULL),
                            (995, 14,102,\'Read Pm from user 102 to user 14 (outbox)\',1731214228,0,4790,NULL,996,NULL),
                            (994, 102,15,\'New Pm from user 102 to user 15 (inbox)\',1731214228,1,328,NULL,NULL,NULL),
                            (993, 15,102,\'New Pm from user 102 to user 15 (outbox)\',1731214228,0,4790,NULL,994,NULL);
        ');
    }
}
