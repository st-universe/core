<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\AllianceSettings;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyType;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\BasicTrade;
use Stu\Orm\Entity\BlockedUser;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\BuildingCost;
use Stu\Orm\Entity\BuildingFieldAlternative;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\BuildingUpgrade;
use Stu\Orm\Entity\BuildingUpgradeCost;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Buoy;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassDeposit;
use Stu\Orm\Entity\ColonyClassResearch;
use Stu\Orm\Entity\ColonyClassRestriction;
use Stu\Orm\Entity\ColonyDepositMining;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\ColonyShipQueue;
use Stu\Orm\Entity\ColonyShipRepair;
use Stu\Orm\Entity\ColonyTerraforming;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\ConstructionProgressModule;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\CrewTraining;
use Stu\Orm\Entity\DatabaseCategory;
use Stu\Orm\Entity\DatabaseCategoryAward;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\DatabaseType;
use Stu\Orm\Entity\DatabaseUser;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\IgnoreList;
use Stu\Orm\Entity\KnCharacter;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostToPlotApplication;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationMining;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\LotteryWinnerBuildplan;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\MiningQueue;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\ModuleSpecial;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\News;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\PartnerSite;
use Stu\Orm\Entity\PirateSetup;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrivateMessage;
use Stu\Orm\Entity\PrivateMessageFolder;
use Stu\Orm\Entity\RepairTask;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\SessionString;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipyardShipQueue;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemType;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\StationShipRepair;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TachyonScan;
use Stu\Orm\Entity\Terraforming;
use Stu\Orm\Entity\TerraformingCost;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\TorpedoHull;
use Stu\Orm\Entity\TorpedoStorage;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\TradeLicense;
use Stu\Orm\Entity\TradeLicenseInfo;
use Stu\Orm\Entity\TradeOffer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\TradeShoutbox;
use Stu\Orm\Entity\TradeTransaction;
use Stu\Orm\Entity\TradeTransfer;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\TutorialStep;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Entity\UserCharacter;
use Stu\Orm\Entity\UserInvitation;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserMap;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\UserReferer;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Entity\UserTag;
use Stu\Orm\Entity\UserTutorial;
use Stu\Orm\Entity\UserPirateRound;
use Stu\Orm\Entity\Weapon;
use Stu\Orm\Entity\WeaponShield;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\WormholeRestriction;

return [
    AllianceRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Alliance::class),
    AllianceBoardRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoard::class),
    AllianceBoardPostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardPost::class),
    AllianceBoardTopicRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardTopic::class),
    AllianceJobRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceJob::class),
    AllianceRelationRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceRelation::class),
    AllianceSettingsRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AllianceSettings::class),
    AnomalyTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AnomalyType::class),
    AnomalyRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Anomaly::class),
    AwardRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Award::class),
    BasicTradeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BasicTrade::class),
    BlockedUserRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BlockedUser::class),
    BuildingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Building::class),
    BuildingCostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingCost::class),
    BuildingFieldAlternativeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingFieldAlternative::class),
    BuildingFunctionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingFunction::class),
    BuildingCommodityRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingCommodity::class),
    BuildingUpgradeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgrade::class),
    BuildingUpgradeCostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgradeCost::class),
    BuildplanHangarRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildplanHangar::class),
    BuildplanModuleRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(BuildplanModule::class),
    BuoyRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Buoy::class),
    ColonyRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Colony::class),
    ColonySandboxRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonySandbox::class),
    ColonyTerraformingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyTerraforming::class),
    CommodityRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Commodity::class),
    ConstructionProgressRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgress::class),
    ConstructionProgressModuleRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgressModule::class),
    ContactRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Contact::class),
    ColonyClassDepositRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyClassDeposit::class),
    ColonyClassRestrictionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyClassRestriction::class),
    ColonyDepositMiningRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyDepositMining::class),
    ColonyShipRepairRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyShipRepair::class),
    ColonyShipQueueRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyShipQueue::class),
    ColonyScanRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyScan::class),
    CrewRaceRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(CrewRace::class),
    CrewRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Crew::class),
    CrewTrainingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(CrewTraining::class),
    DatabaseCategoryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DatabaseCategory::class),
    DatabaseCategoryAwardRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DatabaseCategoryAward::class),
    DatabaseEntryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DatabaseEntry::class),
    DatabaseTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DatabaseType::class),
    DatabaseUserRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DatabaseUser::class),
    DealsRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Deals::class),
    AuctionBidRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AuctionBid::class),
    DockingPrivilegeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(DockingPrivilege::class),
    FactionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Faction::class),
    FleetRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Fleet::class),
    FlightSignatureRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(FlightSignature::class),
    AstroEntryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(AstronomicalEntry::class),
    GameConfigRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(GameConfig::class),
    GameTurnRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(GameTurn::class),
    GameRequestRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(GameRequest::class),
    GameTurnStatsRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(GameTurnStats::class),
    HistoryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(History::class),
    IgnoreListRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(IgnoreList::class),
    KnCharacterRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(KnCharacter::class),
    KnCommentRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(KnComment::class),
    KnPostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(KnPost::class),
    KnPostToPlotApplicationRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(KnPostToPlotApplication::class),
    LayerRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Layer::class),
    LocationRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Location::class),
    LocationMiningRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(LocationMining::class),
    LotteryTicketRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(LotteryTicket::class),
    LotteryWinnerBuildplanRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(LotteryWinnerBuildplan::class),
    MapBorderTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(MapBorderType::class),
    MapFieldTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(MapFieldType::class),
    MapRegionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(MapRegion::class),
    MapRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Map::class),
    MiningQueueRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(MiningQueue::class),
    ModuleBuildingFunctionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ModuleBuildingFunction::class),
    ModuleCostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ModuleCost::class),
    ModuleRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Module::class),
    ModuleQueueRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ModuleQueue::class),
    ModuleSpecialRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ModuleSpecial::class),
    NewsRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(News::class),
    NoteRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Note::class),
    NPCLogRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(NPCLog::class),
    OpenedAdventDoorRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(OpenedAdventDoor::class),
    NamesRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Names::class),
    PartnerSiteRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PartnerSite::class),
    PlanetFieldRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PlanetField::class),
    PlanetFieldTypeBuildingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldTypeBuilding::class),
    PlanetFieldTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldType::class),
    ColonyClassRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyClass::class),
    ColonyClassResearchRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ColonyClassResearch::class),
    PirateSetupRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PirateSetup::class),
    PirateRoundRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PirateRound::class),
    PirateWrathRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PirateWrath::class),
    PrestigeLogRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PrestigeLog::class),
    PrivateMessageRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PrivateMessage::class),
    PrivateMessageFolderRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(PrivateMessageFolder::class),
    RepairTaskRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(RepairTask::class),
    ResearchRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Research::class),
    ResearchedRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Researched::class),
    ResearchDependencyRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ResearchDependency::class),
    RpgPlotRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(RpgPlot::class),
    RpgPlotMemberRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(RpgPlotMember::class),
    SessionStringRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(SessionString::class),
    SpacecraftBuildplanRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(SpacecraftBuildplan::class),
    CrewAssignmentRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(CrewAssignment::class),
    ShipLogRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipLog::class),
    ShipRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Ship::class),
    ShipRumpBuildingFunctionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpBuildingFunction::class),
    ShipRumpCategoryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategory::class),
    ShipRumpCategoryRoleCrewRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategoryRoleCrew::class),
    ShipRumpColonizationBuildingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpColonizationBuilding::class),
    ShipRumpCostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCost::class),
    ShipRumpModuleLevelRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpModuleLevel::class),
    SpacecraftRumpRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(SpacecraftRump::class),
    ShipRumpRoleRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpRole::class),
    ShipRumpSpecialRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpSpecial::class),
    ShipRumpUserRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpUser::class),
    SpacecraftSystemRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(SpacecraftSystem::class),
    ShipTakeoverRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipTakeover::class),
    ShipyardShipQueueRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(ShipyardShipQueue::class),
    SpacecraftRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Spacecraft::class),
    SpacecraftEmergencyRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(SpacecraftEmergency::class),
    StarSystemMapRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(StarSystemMap::class),
    StarSystemRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(StarSystem::class),
    StarSystemTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(StarSystemType::class),
    StationRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Station::class),
    StationShipRepairRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(StationShipRepair::class),
    StorageRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Storage::class),
    TerraformingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Terraforming::class),
    TachyonScanRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TachyonScan::class),
    TerraformingCostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TerraformingCost::class),
    TholianWebRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TholianWeb::class),
    TorpedoHullRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TorpedoHull::class),
    TorpedoTypeRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TorpedoType::class),
    TorpedoStorageRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TorpedoStorage::class),
    TradeLicenseInfoRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeLicenseInfo::class),
    TradeLicenseRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeLicense::class),
    TradeOfferRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeOffer::class),
    TradePostRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradePost::class),
    TradeShoutboxRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeShoutbox::class),
    TradeTransactionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeTransaction::class),
    TradeTransferRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TradeTransfer::class),
    TrumfieldRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Trumfield::class),
    TutorialStepRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(TutorialStep::class),
    UserAwardRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserAward::class),
    UserCharacterRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserCharacter::class),
    UserLayerRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserLayer::class),
    UserLockRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserLock::class),
    UserRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(User::class),
    UserIpTableRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserIpTable::class),
    UserInvitationRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserInvitation::class),
    UserMapRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserMap::class),
    UserProfileVisitorRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserProfileVisitor::class),
    UserPirateRoundRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserPirateRound::class),
    UserRefererRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserReferer::class),
    UserSettingRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserSetting::class),
    UserTagRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserTag::class),
    UserTutorialRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(UserTutorial::class),
    WeaponRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(Weapon::class),
    WeaponShieldRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(WeaponShield::class),
    WormholeEntryRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(WormholeEntry::class),
    WormholeRestrictionRepositoryInterface::class => fn(ContainerInterface $c): EntityRepository => $c->get(EntityManagerInterface::class)->getRepository(WormholeRestriction::class),
];
