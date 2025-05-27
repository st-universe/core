<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUser extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user (id, username, login, pass, email, allys_id, race, lastaction, creation, kn_lez, delmark, vac_active, description, tick, sessiondata, password_token, vac_request_date, mobile, sms_code, state, prestige, maptype, deals, last_boarding)
                VALUES (11, \'[b][color=#055415]Romulanisches Sternenimperium[/color] [/b]\', \'romulaner\', \'pw\', \'npc@stuniverse.de\', NULL, 2, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (13, \'[b][color=#999900]Cardassianische Union[/color][/b]\', \'cardassianer\', \'pw\', \'npc@stuniverse.de\', NULL, 4, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (14, \'[color=#FF8000][b]Ferengi Allianz[/b][/color]\', \'ferengi\', \'pw\', \'npc@stuniverse.de\', NULL, 5, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (15, \'[b][color=552222]Pakled[/color][/b] (NPC)\', \'pakled\', \'pw\', \'npc@stuniverse.de\', NULL, 6, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (17, \'[b][color=#9b734d]Kazon[/color][/b] (KI-NPC)\', \'kazon\', \'pw\', \'npc@stuniverse.de\', NULL, 7, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (19, \'[b][color=#006400]Borg Kollektiv[/color][/b]\', \'borg\', \'pw\', \'npc@stuniverse.de\', NULL, 8, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL),
                       (101, \'Siedler 101\', \'test\', \'$2y$10$0GsVfP3Ok.ngqaqARlyUnOXjZOeSyNEiVM3n1Fi5pnWu8YBGxLvxO\', \'npc@stuniverse.de\', 2, 1, 1732007484, 1731253491, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 1, 1, NULL),
                       (102, \'Siedler 102\', \'test2\', \'$2y$10$0GsVfP3Ok.ngqaqARlyUnOXjZOeSyNEiVM3n1Fi5pnWu8YBGxLvxO\', \'npc@stuniverse.de\', NULL, 1, 1732009965, 1731253673, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 1, 0, NULL),
                       (1, \'Niemand\', \'niemand\', \'pw\', \'npc@stuniverse.de\', NULL, 9, 1710020754, 1248036351, 507, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 1, 19, NULL, 0, NULL),
                       (3, \'Test\', \'tester\', \'pw\', \'npc@stuniverse.de\', NULL, 1, 1611430453, 1248036351, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 1, 0, NULL, 0, NULL),
                       (2, \'Handelsallianz\', \'handelsallianz\', \'pw\', \'npc@stuniverse.de\', NULL, 1, 1611430453, 1248036351, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 1, 0, NULL, 0, NULL),
                       (12, \'[b][color=#760505]Klingonisches Reich[/color][/b]\', \'klingonen\', \'pw\', \'npc@stuniverse.de\', NULL, 3, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 1, NULL),
                       (10, \'[b][color=#6699CC]Vereinte Föderation der Planeten[/color][/b]\', \'föderation\', \'pw\', \'npc@stuniverse.de\', NULL, 1, 1731247407, 1731247407, 0, 3, 0, \'\', 1, \'\', \'\', 0, NULL, NULL, 2, 0, 2, 0, NULL);
        ');
    }
}
