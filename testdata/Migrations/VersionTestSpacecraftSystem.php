<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestSpacecraftSystem extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_spacecraft_system.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_spacecraft_system (id, spacecraft_id, system_type, module_id, status, mode, cooldown, data)
                VALUES (14, 42, 16, NULL, 100, 1, NULL, NULL),
                       (15, 42, 14, NULL, 100, 1, NULL, NULL),
                       (16, 42, 13, NULL, 100, 3, NULL, NULL),
                       (17, 42, 11, 10202, 100, 1, NULL, \'{"shieldRegenerationTimer":0}\'),
                       (19, 42, 2, 10403, 100, 1, NULL, NULL),
                       (20, 42, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (21, 42, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (22, 42, 5, 10702, 100, 2, NULL, \'{"baseDamage":43}\'),
                       (24, 42, 9, NULL, 100, 2, NULL, NULL),
                       (25, 42, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (18, 42, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (4226, 42, 26, NULL, 100, 1, NULL, NULL),
                       (7701, 77, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (7703, 77, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (7704, 77, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (7705, 77, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (7708, 77, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (7710, 77, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (7801, 78, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (7803, 78, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (7804, 78, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (7805, 78, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (7808, 78, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (7810, 78, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (7901, 79, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (7903, 79, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (7904, 79, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (7905, 79, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (7908, 79, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (7910, 79, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (8001, 80, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (8003, 80, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (8004, 80, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (8005, 80, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (8008, 80, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (8010, 80, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (8101, 81, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (8103, 81, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (8104, 81, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (8105, 81, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (8108, 81, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (8109, 81, 9, NULL, 100, 2, NULL, NULL),
                       (8110, 81, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (8131, 81, 31, NULL, 100, 1, NULL, NULL),
                       (23, 42, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (799, 79, 9, NULL, 100, 2, NULL, NULL),
                       (791, 79, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (103101, 1031, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (103103, 1031, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (103104, 1031, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (103105, 1031, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (103108, 1031, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (103110, 1031, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (102101, 1021, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (102103, 1021, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (102104, 1021, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (102105, 1021, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (102108, 1021, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (102110, 1021, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (102128, 1021, 28, NULL, 100, 1, NULL, NULL),
                       (102131, 1021, 31, NULL, 100, 1, NULL, NULL),
                       (102132, 1022, 32, NULL, 100, 1, NULL, NULL),
                       (102328, 1023, 28, NULL, 100, 1, NULL, NULL),
                       (102428, 1024, 28, NULL, 100, 1, NULL, \'{"webUnderConstructionId":60001,"ownedWebId":60001}\'),
                       (102528, 1025, 28, NULL, 100, 1, NULL, \'{"webUnderConstructionId":60001,"ownedWebId":null}\'),
                       (102628, 1026, 28, NULL, 100, 1, NULL, \'{"webUnderConstructionId":null,"ownedWebId":60002}\'),
                       (1000011, 100001, 1, NULL, 100, 1, NULL, \'{"eps":15,"maxEps":20,"maxBattery":3,"battery":1,"batteryCooldown":0,"reloadBattery":false}\'),
                       (1000014, 100001, 4, NULL, 100, 1, NULL, \'{"hitChance":0,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":1}\'),
                       (1000019, 100001, 9, NULL, 100, 1, NULL, NULL),
                       (4301, 43, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (4303, 43, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (4304, 43, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (4305, 43, 5, 10702, 100, 2, NULL, \'{"baseDamage":43}\'),
                       (4306, 43, 6, 10702, 100, 1, NULL, NULL),
                       (4308, 43, 8, 10602, 100, 1, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (4332, 43, 32, 10702, 100, 1, NULL, NULL),
                       (1020301, 10203, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (1020303, 10203, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (1020304, 10203, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (1020305, 10203, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (1020308, 10203, 8, 10602, 100, 2, NULL, \'{"sensorRange":2,"mode":1}\'),
                       (1020310, 10203, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (10210201, 102102, 1, 10302, 100, 1, NULL, \'{"eps":107,"maxEps":108,"maxBattery":36,"battery":36,"batteryCooldown":0,"reloadBattery":false}\'),
                       (10210203, 102102, 3, 10502, 100, 1, NULL, \'{"output":54,"load":810}\'),
                       (10210204, 102102, 4, 11902, 100, 1, NULL, \'{"hitChance":68,"evadeChance":0,"isInEmergency":false,"flightDirection":0,"alertState":2}\'),
                       (10210205, 102102, 5, 10702, 100, 1, NULL, \'{"baseDamage":43}\'),
                       (10210208, 102102, 8, 10602, 100, 2, NULL, \'{"sensorRange":10,"mode":1}\'),
                       (10210210, 102102, 10, 10912, 100, 1, NULL, \'{"wd":57,"maxwd":58,"split":100,"autoCarryOver":0}\'),
                       (10210218, 102102, 18, 10912, 100, 3, NULL, \'{"flightSigId": 1, "spacecraftId": 77, "analyzeTime": 1732214048}\'),
                       (10210219, 102102, 19, 10912, 100, 1, NULL, NULL);
        ');
    }
}
