<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft;

use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Game\Action\Transfer\Transfer;
use Stu\Module\Game\View\ShowTransfer\ShowTransfer;
use Stu\Module\Ship\View\ShowShip\ShipShowStrategy;
use Stu\Module\Spacecraft\Action\ActivateSystem\ActivateSystem;
use Stu\Module\Spacecraft\Action\ActivateTractorBeam\ActivateTractorBeam;
use Stu\Module\Spacecraft\Action\AddShipLog\AddShipLog;
use Stu\Module\Spacecraft\Action\AttackBuilding\AttackBuilding;
use Stu\Module\Spacecraft\Action\AttackSpacecraft\AttackSpacecraft;
use Stu\Module\Spacecraft\Action\BoardShip\BoardShip;
use Stu\Module\Spacecraft\Action\ChangeName\ChangeName;
use Stu\Module\Spacecraft\Action\ChangeName\ChangeNameRequest;
use Stu\Module\Spacecraft\Action\ChangeName\ChangeNameRequestInterface;
use Stu\Module\Spacecraft\Action\DeactivateSystem\DeactivateSystem;
use Stu\Module\Spacecraft\Action\DeactivateTractorBeam\DeactivateTractorBeam;
use Stu\Module\Spacecraft\Action\DoTachyonScan\DoTachyonScan;
use Stu\Module\Spacecraft\Action\DropBuoy\DropBuoy;
use Stu\Module\Spacecraft\Action\DumpForeignCrewman\DumpForeignCrewman;
use Stu\Module\Spacecraft\Action\EnterStarSystem\EnterStarSystem;
use Stu\Module\Spacecraft\Action\EnterWormhole\EnterWormhole;
use Stu\Module\Spacecraft\Action\EpsTransfer\EpsTransfer;
use Stu\Module\Spacecraft\Action\InterceptShip\InterceptShip;
use Stu\Module\Spacecraft\Action\LeaveStarSystem\LeaveStarSystem;
use Stu\Module\Spacecraft\Action\LeaveWormhole\LeaveWormhole;
use Stu\Module\Spacecraft\Action\LoadReactor\LoadReactor;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShip;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipDown;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipLeft;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipRequest;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipRequestInterface;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipRight;
use Stu\Module\Spacecraft\Action\MoveShip\MoveShipUp;
use Stu\Module\Spacecraft\Action\OpenAdventDoor\OpenAdventDoor;
use Stu\Module\Spacecraft\Action\RemoveWaste\RemoveWaste;
use Stu\Module\Spacecraft\Action\RenameCrew\RenameCrew;
use Stu\Module\Spacecraft\Action\RenameCrew\RenameCrewRequest;
use Stu\Module\Spacecraft\Action\RenameCrew\RenameCrewRequestInterface;
use Stu\Module\Spacecraft\Action\SalvageEmergencyPods\ClosestLocations;
use Stu\Module\Spacecraft\Action\SalvageEmergencyPods\SalvageEmergencyPods;
use Stu\Module\Spacecraft\Action\SalvageEmergencyPods\TransferToClosestLocation;
use Stu\Module\Spacecraft\Action\SelfDestruct\SelfDestruct;
use Stu\Module\Spacecraft\Action\Selfrepair\Selfrepair;
use Stu\Module\Spacecraft\Action\SendBroadcast\SendBroadcast;
use Stu\Module\Spacecraft\Action\SetGreenAlert\SetGreenAlert;
use Stu\Module\Spacecraft\Action\SetLSSModeBorder\SetLSSModeBorder;
use Stu\Module\Spacecraft\Action\SetLSSMode\SetLSSMode;
use Stu\Module\Spacecraft\Action\SetRedAlert\SetRedAlert;
use Stu\Module\Spacecraft\Action\SetYellowAlert\SetYellowAlert;
use Stu\Module\Spacecraft\Action\Shutdown\Shutdown;
use Stu\Module\Spacecraft\Action\SplitReactorOutput\SplitReactorOutput;
use Stu\Module\Spacecraft\Action\StartEmergency\StartEmergency;
use Stu\Module\Spacecraft\Action\StartEmergency\StartEmergencyRequest;
use Stu\Module\Spacecraft\Action\StartShuttle\StartShuttle;
use Stu\Module\Spacecraft\Action\StartTakeover\StartTakeover;
use Stu\Module\Spacecraft\Action\StopEmergency\StopEmergency;
use Stu\Module\Spacecraft\Action\StopEmergency\StopEmergencyRequest;
use Stu\Module\Spacecraft\Action\StopTakeover\StopTakeover;
use Stu\Module\Spacecraft\Action\StoreShuttle\StoreShuttle;
use Stu\Module\Spacecraft\Action\TakeBuoy\TakeBuoy;
use Stu\Module\Spacecraft\Action\TransferFromAccount\TransferFromAccount;
use Stu\Module\Spacecraft\Action\TransferToAccount\TransferToAccount;
use Stu\Module\Spacecraft\Action\UnloadBattery\UnloadBattery;
use Stu\Module\Spacecraft\Action\WarpdriveBoost\WarpdriveBoost;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorFactory;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftConfiguratorFactoryInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreator;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCreatorInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactory;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactoryInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftSystemCreation;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftSystemCreationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoader;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\View\Noop\Noop;
use Stu\Module\Spacecraft\View\ShowAlertLevel\ShowAlertLevel;
use Stu\Module\Spacecraft\View\ShowAnalyseBuoy\ShowAnalyseBuoy;
use Stu\Module\Spacecraft\View\ShowColonyScan\ShowColonyScan;
use Stu\Module\Spacecraft\View\ShowEpsTransfer\ShowEpsTransfer;
use Stu\Module\Spacecraft\View\ShowInformation\ShowInformation;
use Stu\Module\Spacecraft\View\ShowLSSFilter\ShowLSSFilter;
use Stu\Module\Spacecraft\View\ShowRegionInfo\ShowRegionInfo;
use Stu\Module\Spacecraft\View\ShowRenameCrew\ShowRenameCrew;
use Stu\Module\Spacecraft\View\ShowRepairOptions\ShowRepairOptions;
use Stu\Module\Spacecraft\View\ShowScan\ShowScan;
use Stu\Module\Spacecraft\View\ShowSectorScan\ShowSectorScan;
use Stu\Module\Spacecraft\View\ShowSelfDestruct\ShowSelfDestruct;
use Stu\Module\Spacecraft\View\ShowShipCommunication\ShowShipCommunication;
use Stu\Module\Spacecraft\View\ShowSpacecraftDetails\ShowSpacecraftDetails;
use Stu\Module\Spacecraft\View\ShowSpacecraftStorage\ShowSpacecraftStorage;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\View\ShowSpacecraft\SpacecraftTypeShowStragegyInterface;
use Stu\Module\Spacecraft\View\ShowWasteMenu\ShowWasteMenu;
use Stu\Module\Station\View\ShowStation\StationShowStrategy;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelper;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdown;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertDetection;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertDetectionInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipInformation;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipInformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipsDetection;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertedShipsDetectionInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacade;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetection;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetectionInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\TrojanHorseNotifier;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\TrojanHorseNotifierInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertLevelBasedReaction;
use Stu\Module\Spacecraft\Lib\Battle\AlertLevelBasedReactionInterface;
use Stu\Module\Spacecraft\Lib\Battle\AttackMatchup;
use Stu\Module\Spacecraft\Lib\Battle\AttackMatchupInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLib;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactory;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactory;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCore;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycle;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackPreparation;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackPreparationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhase;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\TholianWebWeaponPhase;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\TholianWebWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\BoardShipUtil;
use Stu\Module\Spacecraft\Lib\CloseCombat\BoardShipUtilInterface;
use Stu\Module\Spacecraft\Lib\CloseCombat\CloseCombatUtil;
use Stu\Module\Spacecraft\Lib\CloseCombat\CloseCombatUtilInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCorrector;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftCorrectorInterface;
use Stu\Module\Spacecraft\Lib\Crew\LaunchEscapePods;
use Stu\Module\Spacecraft\Lib\Crew\LaunchEscapePodsInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaver;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtility;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamage;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Damage\ApplyFieldDamage;
use Stu\Module\Spacecraft\Lib\Damage\ApplyFieldDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\CancelTakeover;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\ClearTractoringBeam;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\ColonizationShipCheck;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\CrewEvacuation;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\HistoryEntryCreation;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\LeaveIntactModules;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\PrestigeGain;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\ResetTrackerDevices;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\TholianWebDestruction;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\TradepostDestruction;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\TransformToTrumfield;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\UpdatePirateWrath;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestruction;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionChecker;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCore;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManager;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndocking;
use Stu\Module\Spacecraft\Lib\Interaction\ShipUndockingInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ThreatReaction;
use Stu\Module\Spacecraft\Lib\Interaction\ThreatReactionInterface;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManager;
use Stu\Module\Spacecraft\Lib\Interaction\TrackerDeviceManagerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactory;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculator;
use Stu\Module\Spacecraft\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\AlertStateConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\AstroMappingConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\DockConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\DriveActivationConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\DriveDeactivationConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\EpsConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\FlightDirectionConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\FlightStartConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\RepairConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\RetrofitConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\TakeoverConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\TholianWebConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\TractorConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\Flight\WarpdriveConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\AnomalyConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\DeactivateTranswarpConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\DeflectorConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\FieldTypeEffectConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightAstroMappingConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightConsequenceInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightDirectionConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightTrackerConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight\PostFlightTractorConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Component\FlightSignatureCreator;
use Stu\Module\Spacecraft\Lib\Movement\Component\FlightSignatureCreatorInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\BlockedCondition;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\CrewCondition;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\DriveActivatableCondition;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\EnoughEpsCondition;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\EnoughWarpdriveCondition;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\Condition\PreFlightConditionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResultFactory;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResultFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheck;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirection;
use Stu\Module\Spacecraft\Lib\Movement\Component\UpdateFlightDirectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\CheckDestination;
use Stu\Module\Spacecraft\Lib\Movement\Route\CheckDestinationInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\EnterWaypoint;
use Stu\Module\Spacecraft\Lib\Movement\Route\EnterWaypointInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactory;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteFactoryInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\LoadWaypoints;
use Stu\Module\Spacecraft\Lib\Movement\Route\LoadWaypointsInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntry;
use Stu\Module\Spacecraft\Lib\Movement\Route\RandomSystemEntryInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMovementInformationAdder;
use Stu\Module\Spacecraft\Lib\Movement\ShipMovementInformationAdderInterface;
use Stu\Module\Spacecraft\Lib\Movement\ShipMover;
use Stu\Module\Spacecraft\Lib\Movement\ShipMoverInterface;
use Stu\Module\Spacecraft\Lib\ReactorUtil;
use Stu\Module\Spacecraft\Lib\ReactorUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftRemover;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChanger;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactory;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedo;
use Stu\Module\Spacecraft\Lib\Torpedo\ClearTorpedoInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManager;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\Ui\PanelLayerConfiguration;
use Stu\Module\Spacecraft\Lib\Ui\ShipUiFactory;
use Stu\Module\Spacecraft\Lib\Ui\ShipUiFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\View\ShowSystemSettings\AggregrationSystemSettings;
use Stu\Module\Spacecraft\View\ShowSystemSettings\BussardCollectorSystemSettings;
use Stu\Module\Spacecraft\View\ShowSystemSettings\ShowSystemSettings;
use Stu\Module\Spacecraft\View\ShowSystemSettings\SystemSettingsProviderInterface;
use Stu\Module\Spacecraft\View\ShowSystemSettings\WebEmitterSystemSettings;
use Stu\Module\Spacecraft\View\ShowTradeMenu\ShowTradeMenu;
use Stu\Module\Spacecraft\View\ShowTradeMenuTransfer\ShowTradeMenuTransfer;

use function DI\autowire;
use function DI\get;

return [
    MessageFactoryInterface::class => autowire(MessageFactory::class),
    ShipMoverInterface::class => autowire(ShipMover::class),
    ModuleValueCalculatorInterface::class => autowire(ModuleValueCalculator::class),
    InteractionCheckerInterface::class => autowire(InteractionChecker::class),
    ApplyDamageInterface::class => autowire(ApplyDamage::class),
    ApplyFieldDamageInterface::class => autowire(ApplyFieldDamage::class),
    EnergyWeaponPhaseInterface::class => autowire(EnergyWeaponPhase::class),
    FightLibInterface::class => autowire(FightLib::class),
    ProjectileWeaponPhaseInterface::class => autowire(ProjectileWeaponPhase::class),
    TholianWebWeaponPhaseInterface::class => autowire(TholianWebWeaponPhase::class),
    SpacecraftAttackCoreInterface::class => autowire(SpacecraftAttackCore::class),
    SpacecraftAttackPreparationInterface::class => autowire(SpacecraftAttackPreparation::class),
    SpacecraftAttackCycleInterface::class => autowire(SpacecraftAttackCycle::class),
    ActivatorDeactivatorHelperInterface::class => autowire(ActivatorDeactivatorHelper::class),
    BattlePartyFactoryInterface::class => autowire(BattlePartyFactory::class),
    SkipDetectionInterface::class => autowire(SkipDetection::class),
    AlertedShipsDetectionInterface::class => autowire(AlertedShipsDetection::class),
    AlertedShipInformationInterface::class => autowire(AlertedShipInformation::class),
    TrojanHorseNotifierInterface::class => autowire(TrojanHorseNotifier::class),
    AlertDetectionInterface::class => autowire(AlertDetection::class),
    AlertReactionFacadeInterface::class => autowire(AlertReactionFacade::class),
    SpacecraftLeaverInterface::class => autowire(SpacecraftLeaver::class),
    LaunchEscapePodsInterface::class => autowire(LaunchEscapePods::class),
    TroopTransferUtilityInterface::class => autowire(TroopTransferUtility::class),
    SpacecraftRemoverInterface::class => autowire(SpacecraftRemover::class),
    ShipUndockingInterface::class => autowire(ShipUndocking::class),
    SpacecraftShutdownInterface::class => autowire(SpacecraftShutdown::class),
    ThreatReactionInterface::class => autowire(ThreatReaction::class),
    CloseCombatUtilInterface::class => autowire(CloseCombatUtil::class),
    BoardShipUtilInterface::class => autowire(BoardShipUtil::class),
    ReactorUtilInterface::class => autowire(ReactorUtil::class),
    ClearTorpedoInterface::class => autowire(ClearTorpedo::class),
    ShipTorpedoManagerInterface::class => autowire(ShipTorpedoManager::class),
    SpacecraftWrapperFactoryInterface::class => autowire(SpacecraftWrapperFactory::class)
        ->constructorParameter('stateIconAndTitle', autowire(StateIconAndTitle::class)),
    SpacecraftStateChangerInterface::class => autowire(SpacecraftStateChanger::class),
    ShipTakeoverManagerInterface::class => autowire(ShipTakeoverManager::class),
    AttackerProviderFactoryInterface::class => autowire(AttackerProviderFactory::class),
    AttackMatchupInterface::class => autowire(AttackMatchup::class),
    AlertLevelBasedReactionInterface::class => autowire(AlertLevelBasedReaction::class),
    FlightSignatureCreatorInterface::class => autowire(FlightSignatureCreator::class),
    EnterWaypointInterface::class => autowire(EnterWaypoint::class),
    CheckDestinationInterface::class => autowire(CheckDestination::class),
    LoadWaypointsInterface::class => autowire(LoadWaypoints::class),
    UpdateFlightDirectionInterface::class => autowire(UpdateFlightDirection::class),
    RandomSystemEntryInterface::class => autowire(RandomSystemEntry::class),
    ShipMovementInformationAdderInterface::class => autowire(ShipMovementInformationAdder::class),
    InterceptShipCoreInterface::class => autowire(InterceptShipCore::class),
    TrackerDeviceManagerInterface::class => autowire(TrackerDeviceManager::class),
    PreFlightConditionInterface::class => [
        autowire(BlockedCondition::class),
        autowire(CrewCondition::class),
        autowire(DriveActivatableCondition::class),
        autowire(EnoughEpsCondition::class),
        autowire(EnoughWarpdriveCondition::class)
    ],
    ConditionCheckResultFactoryInterface::class => autowire(ConditionCheckResultFactory::class),
    PreFlightConditionsCheckInterface::class => autowire(PreFlightConditionsCheck::class)
        ->constructorParameter(
            'conditions',
            get(PreFlightConditionInterface::class)
        ),
    FlightStartConsequenceInterface::class => [
        autowire(RepairConsequence::class),
        autowire(RetrofitConsequence::class),
        autowire(DockConsequence::class),
        autowire(TakeoverConsequence::class),
        autowire(AstroMappingConsequence::class),
        autowire(TholianWebConsequence::class),
        autowire(TractorConsequence::class),
        autowire(DriveDeactivationConsequence::class),
        autowire(DriveActivationConsequence::class),
        autowire(EpsConsequence::class),
        autowire(WarpdriveConsequence::class),
        autowire(FlightDirectionConsequence::class),
        autowire(AlertStateConsequence::class)
    ],
    PostFlightConsequenceInterface::class => [
        autowire(PostFlightDirectionConsequence::class),
        autowire(PostFlightAstroMappingConsequence::class),
        autowire(DeactivateTranswarpConsequence::class),
        autowire(PostFlightTrackerConsequence::class),
        autowire(PostFlightTractorConsequence::class),
        autowire(DeflectorConsequence::class),
        autowire(FieldTypeEffectConsequence::class),
        autowire(AnomalyConsequence::class)
    ],
    FlightRouteFactoryInterface::class => autowire(FlightRouteFactory::class)
        ->constructorParameter(
            'flightConsequences',
            get(FlightStartConsequenceInterface::class)
        )->constructorParameter(
            'postFlightConsequences',
            get(PostFlightConsequenceInterface::class)
        ),
    MoveShipRequestInterface::class => autowire(MoveShipRequest::class),
    RenameCrewRequestInterface::class => autowire(RenameCrewRequest::class),
    ChangeNameRequestInterface::class => autowire(ChangeNameRequest::class),
    SpacecraftLoaderInterface::class => autowire(SpacecraftLoader::class),
    SpacecraftFactoryInterface::class => autowire(SpacecraftFactory::class),
    SpacecraftConfiguratorFactoryInterface::class => autowire(SpacecraftConfiguratorFactory::class),
    SpacecraftSystemCreationInterface::class => autowire(SpacecraftSystemCreation::class),
    SpacecraftCreatorInterface::class => autowire(SpacecraftCreator::class),
    SpacecraftCorrectorInterface::class => autowire(SpacecraftCorrector::class),
    SpacecraftTypeShowStragegyInterface::class => [
        SpacecraftTypeEnum::SHIP->value => autowire(ShipShowStrategy::class),
        SpacecraftTypeEnum::STATION->value => autowire(StationShowStrategy::class)
    ],
    ShipUiFactoryInterface::class => autowire(ShipUiFactory::class)
        ->constructorParameter('panelLayerConfiguration', autowire(PanelLayerConfiguration::class)),
    SpacecraftDestructionInterface::class => autowire(SpacecraftDestruction::class)
        ->constructorParameter(
            'destructionHandlers',
            [
                autowire(CrewEvacuation::class),
                autowire(HistoryEntryCreation::class),
                autowire(UpdatePirateWrath::class),
                autowire(CancelTakeover::class),
                autowire(LeaveIntactModules::class),
                autowire(ClearTractoringBeam::class),
                autowire(ColonizationShipCheck::class),
                autowire(PrestigeGain::class),
                autowire(ResetTrackerDevices::class),
                autowire(TradepostDestruction::class),
                autowire(TholianWebDestruction::class),
                autowire(TransformToTrumfield::class)
            ],
        ),
    SystemSettingsProviderInterface::class => [
        SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM->value => autowire(AggregrationSystemSettings::class),
        SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR->value => autowire(BussardCollectorSystemSettings::class),
        SpacecraftSystemTypeEnum::THOLIAN_WEB->value => autowire(WebEmitterSystemSettings::class)
    ],
    'SPACECRAFT_ACTIONS' => [
        AddShipLog::ACTION_IDENTIFIER => autowire(AddShipLog::class),
        DumpForeignCrewman::ACTION_IDENTIFIER => autowire(DumpForeignCrewman::class),
        OpenAdventDoor::ACTION_IDENTIFIER => autowire(OpenAdventDoor::class),
        Selfrepair::ACTION_IDENTIFIER => autowire(Selfrepair::class),
        SendBroadcast::ACTION_IDENTIFIER => autowire(SendBroadcast::class),
        SetLSSModeBorder::ACTION_IDENTIFIER => autowire(SetLSSModeBorder::class),
        SetLSSMode::ACTION_IDENTIFIER => autowire(SetLSSMode::class),
        BoardShip::ACTION_IDENTIFIER => autowire(BoardShip::class),
        StartTakeover::ACTION_IDENTIFIER => autowire(StartTakeover::class),
        StopTakeover::ACTION_IDENTIFIER => autowire(StopTakeover::class),
        StoreShuttle::ACTION_IDENTIFIER => autowire(StoreShuttle::class),
        ActivateSystem::ACTION_IDENTIFIER => autowire(ActivateSystem::class),
        DeactivateSystem::ACTION_IDENTIFIER => autowire(DeactivateSystem::class),
        ChangeName::ACTION_IDENTIFIER => autowire(ChangeName::class),
        LeaveStarSystem::ACTION_IDENTIFIER => autowire(LeaveStarSystem::class),
        EnterStarSystem::ACTION_IDENTIFIER => autowire(EnterStarSystem::class),
        EnterWormhole::ACTION_IDENTIFIER => autowire(EnterWormhole::class),
        LeaveWormhole::ACTION_IDENTIFIER => autowire(LeaveWormhole::class),
        MoveShip::ACTION_IDENTIFIER => autowire(MoveShip::class),
        MoveShipUp::ACTION_IDENTIFIER => autowire(MoveShipUp::class),
        MoveShipDown::ACTION_IDENTIFIER => autowire(MoveShipDown::class),
        MoveShipLeft::ACTION_IDENTIFIER => autowire(MoveShipLeft::class),
        MoveShipRight::ACTION_IDENTIFIER => autowire(MoveShipRight::class),
        UnloadBattery::ACTION_IDENTIFIER => autowire(UnloadBattery::class),
        ActivateTractorBeam::ACTION_IDENTIFIER => autowire(ActivateTractorBeam::class),
        DeactivateTractorBeam::ACTION_IDENTIFIER => autowire(DeactivateTractorBeam::class),
        SetGreenAlert::ACTION_IDENTIFIER => autowire(SetGreenAlert::class),
        SetYellowAlert::ACTION_IDENTIFIER => autowire(SetYellowAlert::class),
        SetRedAlert::ACTION_IDENTIFIER => autowire(SetRedAlert::class),
        LoadReactor::ACTION_IDENTIFIER => autowire(LoadReactor::class),
        RenameCrew::ACTION_IDENTIFIER => autowire(RenameCrew::class),
        EpsTransfer::ACTION_IDENTIFIER => autowire(EpsTransfer::class),
        SelfDestruct::ACTION_IDENTIFIER => autowire(SelfDestruct::class),
        AttackBuilding::ACTION_IDENTIFIER => autowire(AttackBuilding::class),
        AttackSpacecraft::ACTION_IDENTIFIER => autowire(AttackSpacecraft::class),
        TakeBuoy::ACTION_IDENTIFIER => autowire(TakeBuoy::class),
        InterceptShip::ACTION_IDENTIFIER => autowire(InterceptShip::class),
        DoTachyonScan::ACTION_IDENTIFIER => autowire(DoTachyonScan::class),
        DropBuoy::ACTION_IDENTIFIER => autowire(DropBuoy::class),
        Shutdown::ACTION_IDENTIFIER => autowire(Shutdown::class),
        Transfer::ACTION_IDENTIFIER => get(Transfer::class),
        SplitReactorOutput::ACTION_IDENTIFIER => autowire(SplitReactorOutput::class),
        StartShuttle::ACTION_IDENTIFIER => autowire(StartShuttle::class),
        StartEmergency::ACTION_IDENTIFIER => autowire(StartEmergency::class)
            ->constructorParameter(
                'startEmergencyRequest',
                autowire(StartEmergencyRequest::class)
            ),
        StopEmergency::ACTION_IDENTIFIER => autowire(StopEmergency::class)
            ->constructorParameter(
                'stopEmergencyRequest',
                autowire(StopEmergencyRequest::class)
            ),
        SalvageEmergencyPods::ACTION_IDENTIFIER => autowire(SalvageEmergencyPods::class)->constructorParameter(
            'transferToClosestLocation',
            autowire(TransferToClosestLocation::class)->constructorParameter(
                'closestLocations',
                autowire(ClosestLocations::class)
            )
        ),
        TransferToAccount::ACTION_IDENTIFIER => autowire(TransferToAccount::class),
        TransferFromAccount::ACTION_IDENTIFIER => autowire(TransferFromAccount::class),
        WarpdriveBoost::ACTION_IDENTIFIER => autowire(WarpdriveBoost::class),
        RemoveWaste::ACTION_IDENTIFIER => autowire(RemoveWaste::class)
    ],
    'SPACECRAFT_VIEWS' => [
        ShowAlertLevel::VIEW_IDENTIFIER => autowire(ShowAlertLevel::class),
        ShowAnalyseBuoy::VIEW_IDENTIFIER => autowire(ShowAnalyseBuoy::class),
        ShowColonyScan::VIEW_IDENTIFIER => autowire(ShowColonyScan::class),
        ShowEpsTransfer::VIEW_IDENTIFIER => autowire(ShowEpsTransfer::class),
        ShowInformation::VIEW_IDENTIFIER => autowire(ShowInformation::class),
        ShowLSSFilter::VIEW_IDENTIFIER => autowire(ShowLSSFilter::class),
        ShowRegionInfo::VIEW_IDENTIFIER => autowire(ShowRegionInfo::class),
        ShowRenameCrew::VIEW_IDENTIFIER => autowire(ShowRenameCrew::class),
        ShowRepairOptions::VIEW_IDENTIFIER => autowire(ShowRepairOptions::class),
        ShowScan::VIEW_IDENTIFIER => autowire(ShowScan::class),
        ShowSectorScan::VIEW_IDENTIFIER => autowire(ShowSectorScan::class),
        ShowSelfDestruct::VIEW_IDENTIFIER => autowire(ShowSelfDestruct::class),
        ShowShipCommunication::VIEW_IDENTIFIER => autowire(ShowShipCommunication::class),
        ShowSpacecraftDetails::VIEW_IDENTIFIER => autowire(ShowSpacecraftDetails::class),
        ShowSpacecraftStorage::VIEW_IDENTIFIER => autowire(ShowSpacecraftStorage::class),
        ShowSpacecraft::VIEW_IDENTIFIER => autowire(ShowSpacecraft::class),
        ShowSystemSettings::VIEW_IDENTIFIER => autowire(ShowSystemSettings::class),
        ShowTradeMenu::VIEW_IDENTIFIER => autowire(ShowTradeMenu::class),
        ShowTradeMenuTransfer::VIEW_IDENTIFIER => autowire(ShowTradeMenuTransfer::class),
        ShowTransfer::VIEW_IDENTIFIER => get(ShowTransfer::class),
        ShowWasteMenu::VIEW_IDENTIFIER => autowire(ShowWasteMenu::class),
        Noop::VIEW_IDENTIFIER => autowire(Noop::class),
    ],
];
