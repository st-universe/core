<?php

declare(strict_types=1);

namespace Stu\Component\Ship;

use Stu\Component\Ship\Crew\ShipCrewCalculator;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Nbs\NbsUtility;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Component\Ship\Repair\CancelRepair;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\Repair\RepairUtil;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\Storage\ShipStorageManager;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\Data\ShipSystemDataFactory;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManager;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\AstroLaboratoryShipSystem;
use Stu\Component\Ship\System\Type\BeamBlockerShipSystem;
use Stu\Component\Ship\System\Type\CloakShipSystem;
use Stu\Component\Ship\System\Type\ComputerShipSystem;
use Stu\Component\Ship\System\Type\ConstructionHubShipSystem;
use Stu\Component\Ship\System\Type\DeflectorShipSystem;
use Stu\Component\Ship\System\Type\EnergyWeaponShipSystem;
use Stu\Component\Ship\System\Type\EpsShipSystem;
use Stu\Component\Ship\System\Type\FusionReactorShipSystem;
use Stu\Component\Ship\System\Type\HullShipSystem;
use Stu\Component\Ship\System\Type\ImpulseDriveShipSystem;
use Stu\Component\Ship\System\Type\LifeSupportShipSystem;
use Stu\Component\Ship\System\Type\LongRangeScannerShipSystem;
use Stu\Component\Ship\System\Type\MatrixScannerShipSystem;
use Stu\Component\Ship\System\Type\NearFieldScannerShipSystem;
use Stu\Component\Ship\System\Type\ProjectileWeaponShipSystem;
use Stu\Component\Ship\System\Type\RPGShipSystem;
use Stu\Component\Ship\System\Type\ShieldShipSystem;
use Stu\Component\Ship\System\Type\ShuttleRampShipSystem;
use Stu\Component\Ship\System\Type\SubspaceSensorShipSystem;
use Stu\Component\Ship\System\Type\TachyonScannerShipSystem;
use Stu\Component\Ship\System\Type\TorpedoStorageShipSystem;
use Stu\Component\Ship\System\Type\TrackerShipSystem;
use Stu\Component\Ship\System\Type\TractorBeamShipSystem;
use Stu\Component\Ship\System\Type\TranswarpCoilShipSystem;
use Stu\Component\Ship\System\Type\TroopQuartersShipSystem;
use Stu\Component\Ship\System\Type\UplinkShipSystem;
use Stu\Component\Ship\System\Type\WarpcoreShipSystem;
use Stu\Component\Ship\System\Type\WarpdriveShipSystem;
use Stu\Component\Ship\System\Type\WebEmitterShipSystem;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Ship\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\StuTime;

use function DI\autowire;
use function DI\create;

return [
    ShipStorageManagerInterface::class => autowire(ShipStorageManager::class),
    NbsUtilityInterface::class => autowire(NbsUtility::class),
    RepairUtilInterface::class => autowire(RepairUtil::class),
    CancelRepairInterface::class => autowire(CancelRepair::class),
    TractorMassPayloadUtilInterface::class => autowire(TractorMassPayloadUtil::class),
    ShipSystemDataFactoryInterface::class => autowire(ShipSystemDataFactory::class),
    ShipSystemManagerInterface::class => create(ShipSystemManager::class)->constructor(
        [
            ShipSystemTypeEnum::SYSTEM_HULL => autowire(HullShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_CLOAK => autowire(CloakShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_SHIELDS => autowire(ShieldShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE => autowire(WarpdriveShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_NBS => autowire(NearFieldScannerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_PHASER => autowire(EnergyWeaponShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT => autowire(LifeSupportShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TACHYON_SCANNER => autowire(TachyonScannerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TORPEDO => autowire(ProjectileWeaponShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_LSS => autowire(LongRangeScannerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM => autowire(TractorBeamShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_WARPCORE => autowire(WarpcoreShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_EPS => autowire(EpsShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE => autowire(ImpulseDriveShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_COMPUTER => autowire(ComputerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS => autowire(TroopQuartersShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_DEFLECTOR => autowire(DeflectorShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER => autowire(SubspaceSensorShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY => autowire(AstroLaboratoryShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_MATRIX_SCANNER => autowire(MatrixScannerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE => autowire(TorpedoStorageShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP => autowire(ShuttleRampShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_BEAM_BLOCKER => autowire(BeamBlockerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_CONSTRUCTION_HUB => autowire(ConstructionHubShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_UPLINK => autowire(UplinkShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR => autowire(FusionReactorShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL => autowire(TranswarpCoilShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_TRACKER => autowire(TrackerShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB => autowire(WebEmitterShipSystem::class),
            ShipSystemTypeEnum::SYSTEM_RPG_MODULE => autowire(RPGShipSystem::class)
        ],
        autowire(StuTime::class)
    ),
    ShipCrewCalculatorInterface::class => autowire(ShipCrewCalculator::class),
];
