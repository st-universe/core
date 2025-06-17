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
        $this->addSql('INSERT INTO stu_user (id, username, allys_id, race, lastaction, kn_lez, vac_active, description, sessiondata, vac_request_date, state, prestige, deals, last_boarding)
                VALUES (11, \'[b][color=#055415]Romulanisches Sternenimperium[/color] [/b]\', NULL, 2, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (13, \'[b][color=#999900]Cardassianische Union[/color][/b]\', NULL, 4, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (14, \'[color=#FF8000][b]Ferengi Allianz[/b][/color]\', NULL, 5, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (15, \'[b][color=552222]Pakled[/color][/b] (NPC)\', NULL, 6, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (17, \'[b][color=#9b734d]Kazon[/color][/b] (KI-NPC)\', NULL, 7, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (19, \'[b][color=#006400]Borg Kollektiv[/color][/b]\', NULL, 8, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (101, \'Siedler 101\', 2, 1, 1732007484, 0, 0, \'\', \'\', 0, 2, 0, 1, NULL),
                       (102, \'Siedler 102\', NULL, 1, 1732009965, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL),
                       (1, \'Niemand\', NULL, 9, 1710020754, 507, 0, \'\', \'\', 0, 1, 19, 0, NULL),
                       (3, \'Test\', NULL, 1, 1611430453, 0, 0, \'\', \'\', 0, 1, 0, 0, NULL),
                       (2, \'Handelsallianz\', NULL, 1, 1611430453, 0, 0, \'\', \'\', 0, 1, 0, 0, NULL),
                       (12, \'[b][color=#760505]Klingonisches Reich[/color][/b]\', NULL, 3, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 1, NULL),
                       (10, \'[b][color=#6699CC]Vereinte Föderation der Planeten[/color][/b]\', NULL, 1, 1731247407, 0, 0, \'\', \'\', 0, 2, 0, 0, NULL);
        ');
    }
}
