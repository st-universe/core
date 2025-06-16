<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserRegistration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_registration.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_registration (user_id, login, pass, email, creation, delmark, password_token, mobile, sms_code)
                VALUES (11, \'romulaner\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (13, \'cardassianer\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (14, \'ferengi\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (15, \'pakled\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (17, \'kazon\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (19, \'borg\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'MY_TOKEN\', NULL, NULL),
                       (101, \'test\', \'$2y$10$0GsVfP3Ok.ngqaqARlyUnOXjZOeSyNEiVM3n1Fi5pnWu8YBGxLvxO\', \'npc@stuniverse.de\', 1731253491, 3, \'\', NULL, NULL),
                       (102, \'test2\', \'$2y$10$0GsVfP3Ok.ngqaqARlyUnOXjZOeSyNEiVM3n1Fi5pnWu8YBGxLvxO\', \'npc@stuniverse.de\', 1731253673, 3, \'\', NULL, NULL),
                       (1, \'niemand\', \'pw\', \'npc@stuniverse.de\', 1248036351, 3, \'\', NULL, NULL),
                       (3, \'tester\', \'pw\', \'npc@stuniverse.de\', 1248036351, 3, \'\', NULL, NULL),
                       (2, \'handelsallianz\', \'pw\', \'npc@stuniverse.de\', 1248036351, 3, \'\', NULL, NULL),
                       (12, \'klingonen\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL),
                       (10, \'föderation\', \'pw\', \'npc@stuniverse.de\', 1731247407, 3, \'\', NULL, NULL);
        ');
    }
}
