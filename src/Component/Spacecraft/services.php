<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreation;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculator;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\Event\Listener\WarpdriveActivationSubscriber;
use Stu\Component\Spacecraft\Module\ModuleRecycling;
use Stu\Component\Spacecraft\Module\ModuleRecyclingInterface;
use Stu\Component\Spacecraft\Nbs\NbsUtility;
use Stu\Component\Spacecraft\Nbs\NbsUtilityInterface;
use Stu\Component\Spacecraft\Repair\CancelRepair;
use Stu\Component\Spacecraft\Repair\CancelRepairInterface;
use Stu\Component\Spacecraft\Repair\RepairUtil;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\Data\ShipSystemDataFactory;
use Stu\Component\Spacecraft\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManager;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactory;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactoryInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializer;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Component\Spacecraft\System\Type\AggregationSystemShipSystem;
use Stu\Component\Spacecraft\System\Type\AstroLaboratoryShipSystem;
use Stu\Component\Spacecraft\System\Type\BeamBlockerShipSystem;
use Stu\Component\Spacecraft\System\Type\BussardCollectorShipSystem;
use Stu\Component\Spacecraft\System\Type\CloakShipSystem;
use Stu\Component\Spacecraft\System\Type\ComputerShipSystem;
use Stu\Component\Spacecraft\System\Type\ConstructionHubShipSystem;
use Stu\Component\Spacecraft\System\Type\DeflectorShipSystem;
use Stu\Component\Spacecraft\System\Type\EnergyWeaponShipSystem;
use Stu\Component\Spacecraft\System\Type\EpsShipSystem;
use Stu\Component\Spacecraft\System\Type\FusionReactorShipSystem;
use Stu\Component\Spacecraft\System\Type\HullShipSystem;
use Stu\Component\Spacecraft\System\Type\ImpulseDriveShipSystem;
use Stu\Component\Spacecraft\System\Type\LifeSupportShipSystem;
use Stu\Component\Spacecraft\System\Type\LongRangeScannerShipSystem;
use Stu\Component\Spacecraft\System\Type\MatrixScannerShipSystem;
use Stu\Component\Spacecraft\System\Type\NearFieldScannerShipSystem;
use Stu\Component\Spacecraft\System\Type\ProjectileWeaponShipSystem;
use Stu\Component\Spacecraft\System\Type\RPGShipSystem;
use Stu\Component\Spacecraft\System\Type\ShieldShipSystem;
use Stu\Component\Spacecraft\System\Type\ShuttleRampShipSystem;
use Stu\Component\Spacecraft\System\Type\SingularityShipSystem;
use Stu\Component\Spacecraft\System\Type\SubspaceSensorShipSystem;
use Stu\Component\Spacecraft\System\Type\TachyonScannerShipSystem;
use Stu\Component\Spacecraft\System\Type\TorpedoStorageShipSystem;
use Stu\Component\Spacecraft\System\Type\TrackerShipSystem;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Component\Spacecraft\System\Type\TranswarpCoilShipSystem;
use Stu\Component\Spacecraft\System\Type\TroopQuartersShipSystem;
use Stu\Component\Spacecraft\System\Type\UplinkShipSystem;
use Stu\Component\Spacecraft\System\Type\WarpcoreShipSystem;
use Stu\Component\Spacecraft\System\Type\WarpdriveBoosterShipSystem;
use Stu\Component\Spacecraft\System\Type\WarpdriveShipSystem;
use Stu\Component\Spacecraft\System\Type\WebEmitterShipSystem;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtil;
use Stu\Component\Spacecraft\System\Utility\TractorMassPayloadUtilInterface;
use Stu\Module\Control\StuTime;

use function DI\autowire;
use function DI\create;

return [
    NbsUtilityInterface::class => autowire(NbsUtility::class),
    RepairUtilInterface::class => autowire(RepairUtil::class),
    CancelRepairInterface::class => autowire(CancelRepair::class),
    TractorMassPayloadUtilInterface::class => autowire(TractorMassPayloadUtil::class),
    ShipSystemDataFactoryInterface::class => autowire(ShipSystemDataFactory::class),
    SystemDataDeserializerInterface::class => autowire(SystemDataDeserializer::class),
    SpacecraftSystemManagerInterface::class => create(SpacecraftSystemManager::class)->constructor(
        [
            SpacecraftSystemTypeEnum::HULL->value => autowire(HullShipSystem::class),
            SpacecraftSystemTypeEnum::CLOAK->value => autowire(CloakShipSystem::class),
            SpacecraftSystemTypeEnum::SHIELDS->value => autowire(ShieldShipSystem::class),
            SpacecraftSystemTypeEnum::WARPDRIVE->value => autowire(WarpdriveShipSystem::class),
            SpacecraftSystemTypeEnum::NBS->value => autowire(NearFieldScannerShipSystem::class),
            SpacecraftSystemTypeEnum::PHASER->value => autowire(EnergyWeaponShipSystem::class),
            SpacecraftSystemTypeEnum::LIFE_SUPPORT->value => autowire(LifeSupportShipSystem::class),
            SpacecraftSystemTypeEnum::TACHYON_SCANNER->value => autowire(TachyonScannerShipSystem::class),
            SpacecraftSystemTypeEnum::TORPEDO->value => autowire(ProjectileWeaponShipSystem::class),
            SpacecraftSystemTypeEnum::LSS->value => autowire(LongRangeScannerShipSystem::class),
            SpacecraftSystemTypeEnum::TRACTOR_BEAM->value => autowire(TractorBeamShipSystem::class),
            SpacecraftSystemTypeEnum::WARPCORE->value => autowire(WarpcoreShipSystem::class),
            SpacecraftSystemTypeEnum::EPS->value => autowire(EpsShipSystem::class),
            SpacecraftSystemTypeEnum::IMPULSEDRIVE->value => autowire(ImpulseDriveShipSystem::class),
            SpacecraftSystemTypeEnum::COMPUTER->value => autowire(ComputerShipSystem::class),
            SpacecraftSystemTypeEnum::TROOP_QUARTERS->value => autowire(TroopQuartersShipSystem::class),
            SpacecraftSystemTypeEnum::DEFLECTOR->value => autowire(DeflectorShipSystem::class),
            SpacecraftSystemTypeEnum::SUBSPACE_SCANNER->value => autowire(SubspaceSensorShipSystem::class),
            SpacecraftSystemTypeEnum::ASTRO_LABORATORY->value => autowire(AstroLaboratoryShipSystem::class),
            SpacecraftSystemTypeEnum::MATRIX_SCANNER->value => autowire(MatrixScannerShipSystem::class),
            SpacecraftSystemTypeEnum::TORPEDO_STORAGE->value => autowire(TorpedoStorageShipSystem::class),
            SpacecraftSystemTypeEnum::SHUTTLE_RAMP->value => autowire(ShuttleRampShipSystem::class),
            SpacecraftSystemTypeEnum::BEAM_BLOCKER->value => autowire(BeamBlockerShipSystem::class),
            SpacecraftSystemTypeEnum::CONSTRUCTION_HUB->value => autowire(ConstructionHubShipSystem::class),
            SpacecraftSystemTypeEnum::UPLINK->value => autowire(UplinkShipSystem::class),
            SpacecraftSystemTypeEnum::FUSION_REACTOR->value => autowire(FusionReactorShipSystem::class),
            SpacecraftSystemTypeEnum::TRANSWARP_COIL->value => autowire(TranswarpCoilShipSystem::class),
            SpacecraftSystemTypeEnum::TRACKER->value => autowire(TrackerShipSystem::class),
            SpacecraftSystemTypeEnum::THOLIAN_WEB->value => autowire(WebEmitterShipSystem::class),
            SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR->value => autowire(BussardCollectorShipSystem::class),
            SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM->value => autowire(AggregationSystemShipSystem::class),
            SpacecraftSystemTypeEnum::RPG_MODULE->value => autowire(RPGShipSystem::class),
            SpacecraftSystemTypeEnum::SINGULARITY_REACTOR->value => autowire(SingularityShipSystem::class),
            SpacecraftSystemTypeEnum::WARPDRIVE_BOOSTER->value => autowire(WarpdriveBoosterShipSystem::class)
        ],
        autowire(StuTime::class)
    ),
    ModuleRecyclingInterface::class => autowire(ModuleRecycling::class),
    SpacecraftCrewCalculatorInterface::class => autowire(SpacecraftCrewCalculator::class),
    BuildplanSignatureCreationInterface::class => autowire(BuildplanSignatureCreation::class),
    SpacecraftSystemWrapperFactoryInterface::class => autowire(SpacecraftSystemWrapperFactory::class),
    WarpdriveActivationSubscriber::class => autowire()
];
