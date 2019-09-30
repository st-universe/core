<?php

declare(strict_types=1);

namespace Stu\Module\Trade;

use Stu\Component\Ship\System\Type\CloakShipSystem;
use Stu\Component\Ship\System\Type\EnergyWeaponShipSystem;
use Stu\Component\Ship\System\Type\LongRangeScannerShipSystem;
use Stu\Component\Ship\System\Type\NearFieldScannerShipSystem;
use Stu\Component\Ship\System\Type\ProjectileWeaponShipSystem;
use Stu\Component\Ship\System\Type\ShieldShipSystem;
use Stu\Component\Ship\System\ShipSystemManager;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\WarpdriveShipSystem;
use function DI\autowire;
use function DI\create;

return [
    ShipSystemManagerInterface::class => create(ShipSystemManager::class)->constructor(
        [
            ShipSystemTypeEnum::SYSTEM_SHIELDS => autowire(ShieldShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE => autowire(WarpdriveShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_NBS => autowire(NearFieldScannerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_PHASER => autowire(EnergyWeaponShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_CLOAK => autowire(CloakShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TORPEDO => autowire(ProjectileWeaponShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_LSS => autowire(LongRangeScannerShipSystem::class),
        ]
    )
];
