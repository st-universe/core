<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceRelation;
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
use Stu\Orm\Entity\KnCharacters;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostToPlotApplication;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\LotteryTicket;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\ModuleQueue;
use Stu\Orm\Entity\ModuleSpecial;
use Stu\Orm\Entity\News;
use Stu\Orm\Entity\Note;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\PartnerSite;
use Stu\Orm\Entity\PirateSetup;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\PlanetFieldType;
use Stu\Orm\Entity\PlanetFieldTypeBuilding;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\PirateWrath;
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
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpColonizationBuilding;
use Stu\Orm\Entity\ShipRumpCost;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipyardShipQueue;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemType;
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
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserAward;
use Stu\Orm\Entity\UserCharacters;
use Stu\Orm\Entity\UserInvitation;
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserMap;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Entity\UserTag;
use Stu\Orm\Entity\Weapon;
use Stu\Orm\Entity\WeaponShield;
use Stu\Orm\Entity\WormholeEntry;

return [
    AllianceRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Alliance::class);
    },
    AllianceBoardRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceBoardRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AllianceBoard::class);
    },
    AllianceBoardPostRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceBoardPostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardPost::class);
    },
    AllianceBoardTopicRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceBoardTopicRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardTopic::class);
    },
    AllianceJobRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceJobRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AllianceJob::class);
    },
    AllianceRelationRepositoryInterface::class => function (
        ContainerInterface $c
    ): AllianceRelationRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AllianceRelation::class);
    },
    AnomalyTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): AnomalyTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AnomalyType::class);
    },
    AnomalyRepositoryInterface::class => function (
        ContainerInterface $c
    ): AnomalyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Anomaly::class);
    },
    AwardRepositoryInterface::class => function (
        ContainerInterface $c
    ): AwardRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Award::class);
    },
    BasicTradeRepositoryInterface::class => function (
        ContainerInterface $c
    ): BasicTradeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BasicTrade::class);
    },
    BlockedUserRepositoryInterface::class => function (
        ContainerInterface $c
    ): BlockedUserRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BlockedUser::class);
    },
    BuildingRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Building::class);
    },
    BuildingCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingCost::class);
    },
    BuildingFieldAlternativeRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingFieldAlternativeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingFieldAlternative::class);
    },
    BuildingFunctionRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingFunctionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingFunction::class);
    },
    BuildingCommodityRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingCommodityRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingCommodity::class);
    },
    BuildingUpgradeRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingUpgradeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgrade::class);
    },
    BuildingUpgradeCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildingUpgradeCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgradeCost::class);
    },
    BuildplanHangarRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildplanHangarRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildplanHangar::class);
    },
    BuildplanModuleRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuildplanModuleRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(BuildplanModule::class);
    },
    BuoyRepositoryInterface::class => function (
        ContainerInterface $c
    ): BuoyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Buoy::class);
    },
    ColonyRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Colony::class);
    },
    ColonySandboxRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonySandboxRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonySandbox::class);
    },
    ColonyTerraformingRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyTerraformingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyTerraforming::class);
    },
    CommodityRepositoryInterface::class => function (
        ContainerInterface $c
    ): CommodityRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Commodity::class);
    },
    ConstructionProgressRepositoryInterface::class => function (
        ContainerInterface $c
    ): ConstructionProgressRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgress::class);
    },
    ConstructionProgressModuleRepositoryInterface::class => function (
        ContainerInterface $c
    ): ConstructionProgressModuleRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgressModule::class);
    },
    ContactRepositoryInterface::class => function (
        ContainerInterface $c
    ): ContactRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Contact::class);
    },
    ColonyClassDepositRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyClassDepositRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyClassDeposit::class);
    },
    ColonyDepositMiningRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyDepositMiningRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyDepositMining::class);
    },
    ColonyShipRepairRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyShipRepairRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyShipRepair::class);
    },
    ColonyShipQueueRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyShipQueueRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyShipQueue::class);
    },
    ColonyScanRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyScanRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyScan::class);
    },
    CrewRaceRepositoryInterface::class => function (
        ContainerInterface $c
    ): CrewRaceRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(CrewRace::class);
    },
    CrewRepositoryInterface::class => function (
        ContainerInterface $c
    ): CrewRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Crew::class);
    },
    CrewTrainingRepositoryInterface::class => function (
        ContainerInterface $c
    ): CrewTrainingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(CrewTraining::class);
    },
    DatabaseCategoryRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseCategoryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseCategory::class);
    },
    DatabaseEntryRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseEntryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseEntry::class);
    },
    DatabaseTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseType::class);
    },
    DatabaseUserRepositoryInterface::class => function (
        ContainerInterface $c
    ): DatabaseUserRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DatabaseUser::class);
    },
    DealsRepositoryInterface::class => function (
        ContainerInterface $c
    ): DealsRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Deals::class);
    },
    AuctionBidRepositoryInterface::class => function (
        ContainerInterface $c
    ): AuctionBidRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AuctionBid::class);
    },
    DockingPrivilegeRepositoryInterface::class => function (
        ContainerInterface $c
    ): DockingPrivilegeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(DockingPrivilege::class);
    },
    FactionRepositoryInterface::class => function (
        ContainerInterface $c
    ): FactionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Faction::class);
    },
    FleetRepositoryInterface::class => function (
        ContainerInterface $c
    ): FleetRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Fleet::class);
    },
    FlightSignatureRepositoryInterface::class => function (
        ContainerInterface $c
    ): FlightSignatureRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(FlightSignature::class);
    },
    AstroEntryRepositoryInterface::class => function (
        ContainerInterface $c
    ): AstroEntryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(AstronomicalEntry::class);
    },
    GameConfigRepositoryInterface::class => function (
        ContainerInterface $c
    ): GameConfigRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(GameConfig::class);
    },
    GameTurnRepositoryInterface::class => function (
        ContainerInterface $c
    ): GameTurnRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(GameTurn::class);
    },
    GameRequestRepositoryInterface::class => function (
        ContainerInterface $c
    ): GameRequestRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(GameRequest::class);
    },
    GameTurnStatsRepositoryInterface::class => function (
        ContainerInterface $c
    ): GameTurnStatsRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(GameTurnStats::class);
    },
    HistoryRepositoryInterface::class => function (
        ContainerInterface $c
    ): HistoryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(History::class);
    },
    IgnoreListRepositoryInterface::class => function (
        ContainerInterface $c
    ): IgnoreListRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(IgnoreList::class);
    },
    KnCharactersRepositoryInterface::class => function (
        ContainerInterface $c
    ): KnCharactersRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(KnCharacters::class);
    },
    KnCommentRepositoryInterface::class => function (
        ContainerInterface $c
    ): KnCommentRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(KnComment::class);
    },
    KnPostRepositoryInterface::class => function (
        ContainerInterface $c
    ): KnPostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(KnPost::class);
    },
    KnPostToPlotApplicationRepositoryInterface::class => function (
        ContainerInterface $c
    ): KnPostToPlotApplicationRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(KnPostToPlotApplication::class);
    },
    LayerRepositoryInterface::class => function (
        ContainerInterface $c
    ): LayerRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Layer::class);
    },
    LotteryTicketRepositoryInterface::class => function (
        ContainerInterface $c
    ): LotteryTicketRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(LotteryTicket::class);
    },
    MapBorderTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): MapBorderTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(MapBorderType::class);
    },
    MapFieldTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): MapFieldTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(MapFieldType::class);
    },
    MapRegionRepositoryInterface::class => function (
        ContainerInterface $c
    ): MapRegionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(MapRegion::class);
    },
    MapRepositoryInterface::class => function (
        ContainerInterface $c
    ): MapRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Map::class);
    },
    ModuleBuildingFunctionRepositoryInterface::class => function (
        ContainerInterface $c
    ): ModuleBuildingFunctionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ModuleBuildingFunction::class);
    },
    ModuleCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): ModuleCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ModuleCost::class);
    },
    ModuleRepositoryInterface::class => function (
        ContainerInterface $c
    ): ModuleRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Module::class);
    },
    ModuleQueueRepositoryInterface::class => function (
        ContainerInterface $c
    ): ModuleQueueRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ModuleQueue::class);
    },
    ModuleSpecialRepositoryInterface::class => function (
        ContainerInterface $c
    ): ModuleSpecialRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ModuleSpecial::class);
    },
    NewsRepositoryInterface::class => function (
        ContainerInterface $c
    ): NewsRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(News::class);
    },
    NoteRepositoryInterface::class => function (
        ContainerInterface $c
    ): NoteRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Note::class);
    },
    NPCLogRepositoryInterface::class => function (
        ContainerInterface $c
    ): NPCLogRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(NPCLog::class);
    },
    OpenedAdventDoorRepositoryInterface::class => function (
        ContainerInterface $c
    ): OpenedAdventDoorRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(OpenedAdventDoor::class);
    },
    NamesRepositoryInterface::class => function (
        ContainerInterface $c
    ): NamesRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Names::class);
    },
    PartnerSiteRepositoryInterface::class => function (
        ContainerInterface $c
    ): PartnerSiteRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PartnerSite::class);
    },
    PlanetFieldRepositoryInterface::class => function (
        ContainerInterface $c
    ): PlanetFieldRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PlanetField::class);
    },
    PlanetFieldTypeBuildingRepositoryInterface::class => function (
        ContainerInterface $c
    ): PlanetFieldTypeBuildingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldTypeBuilding::class);
    },
    PlanetFieldTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): PlanetFieldTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldType::class);
    },
    ColonyClassRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyClassRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyClass::class);
    },
    ColonyClassResearchRepositoryInterface::class => function (
        ContainerInterface $c
    ): ColonyClassResearchRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ColonyClassResearch::class);
    },
    PirateSetupRepositoryInterface::class => function (
        ContainerInterface $c
    ): PirateSetupRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PirateSetup::class);
    },
    PirateWrathRepositoryInterface::class => function (
        ContainerInterface $c
    ): PirateWrathRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PirateWrath::class);
    },
    PrestigeLogRepositoryInterface::class => function (
        ContainerInterface $c
    ): PrestigeLogRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PrestigeLog::class);
    },
    PrivateMessageRepositoryInterface::class => function (
        ContainerInterface $c
    ): PrivateMessageRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PrivateMessage::class);
    },
    PrivateMessageFolderRepositoryInterface::class => function (
        ContainerInterface $c
    ): PrivateMessageFolderRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(PrivateMessageFolder::class);
    },
    RepairTaskRepositoryInterface::class => function (
        ContainerInterface $c
    ): RepairTaskRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(RepairTask::class);
    },
    ResearchRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Research::class);
    },
    ResearchedRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchedRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Researched::class);
    },
    ResearchDependencyRepositoryInterface::class => function (
        ContainerInterface $c
    ): ResearchDependencyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ResearchDependency::class);
    },
    RpgPlotRepositoryInterface::class => function (
        ContainerInterface $c
    ): RpgPlotRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(RpgPlot::class);
    },
    RpgPlotMemberRepositoryInterface::class => function (
        ContainerInterface $c
    ): RpgPlotMemberRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(RpgPlotMember::class);
    },
    SessionStringRepositoryInterface::class => function (
        ContainerInterface $c
    ): SessionStringRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(SessionString::class);
    },
    ShipBuildplanRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipBuildplanRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipBuildplan::class);
    },
    ShipCrewRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipCrewRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipCrew::class);
    },
    ShipLogRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipLogRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipLog::class);
    },
    ShipRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Ship::class);
    },
    ShipRumpBuildingFunctionRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpBuildingFunctionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpBuildingFunction::class);
    },
    ShipRumpCategoryRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpCategoryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategory::class);
    },
    ShipRumpCategoryRoleCrewRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpCategoryRoleCrewRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategoryRoleCrew::class);
    },
    ShipRumpColonizationBuildingRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpColonizationBuildingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpColonizationBuilding::class);
    },
    ShipRumpCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCost::class);
    },
    ShipRumpModuleLevelRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpModuleLevelRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpModuleLevel::class);
    },
    ShipRumpRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRump::class);
    },
    ShipRumpRoleRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpRoleRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpRole::class);
    },
    ShipRumpSpecialRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpSpecialRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpSpecial::class);
    },
    ShipRumpUserRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipRumpUserRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipRumpUser::class);
    },
    ShipSystemRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipSystemRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipSystem::class);
    },
    ShipTakeoverRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipTakeoverRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipTakeover::class);
    },
    ShipyardShipQueueRepositoryInterface::class => function (
        ContainerInterface $c
    ): ShipyardShipQueueRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(ShipyardShipQueue::class);
    },
    SpacecraftEmergencyRepositoryInterface::class => function (
        ContainerInterface $c
    ): SpacecraftEmergencyRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(SpacecraftEmergency::class);
    },
    StarSystemMapRepositoryInterface::class => function (
        ContainerInterface $c
    ): StarSystemMapRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(StarSystemMap::class);
    },
    StarSystemRepositoryInterface::class => function (
        ContainerInterface $c
    ): StarSystemRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(StarSystem::class);
    },
    StarSystemTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): StarSystemTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(StarSystemType::class);
    },
    StationShipRepairRepositoryInterface::class => function (
        ContainerInterface $c
    ): StationShipRepairRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(StationShipRepair::class);
    },
    StorageRepositoryInterface::class => function (
        ContainerInterface $c
    ): StorageRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Storage::class);
    },
    TerraformingRepositoryInterface::class => function (
        ContainerInterface $c
    ): TerraformingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Terraforming::class);
    },
    TachyonScanRepositoryInterface::class => function (
        ContainerInterface $c
    ): TachyonScanRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TachyonScan::class);
    },
    TerraformingCostRepositoryInterface::class => function (
        ContainerInterface $c
    ): TerraformingCostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TerraformingCost::class);
    },
    TholianWebRepositoryInterface::class => function (
        ContainerInterface $c
    ): TholianWebRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TholianWeb::class);
    },
    TorpedoHullRepositoryInterface::class => function (
        ContainerInterface $c
    ): TorpedoHullRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TorpedoHull::class);
    },
    TorpedoTypeRepositoryInterface::class => function (
        ContainerInterface $c
    ): TorpedoTypeRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TorpedoType::class);
    },
    TorpedoStorageRepositoryInterface::class => function (
        ContainerInterface $c
    ): TorpedoStorageRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TorpedoStorage::class);
    },
    TradeLicenseInfoRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeLicenseInfoRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeLicenseInfo::class);
    },
    TradeLicenseRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeLicenseRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeLicense::class);
    },
    TradeOfferRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeOfferRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeOffer::class);
    },
    TradePostRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradePostRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradePost::class);
    },
    TradeShoutboxRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeShoutboxRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeShoutbox::class);
    },
    TradeTransactionRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeTransactionRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeTransaction::class);
    },
    TradeTransferRepositoryInterface::class => function (
        ContainerInterface $c
    ): TradeTransferRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(TradeTransfer::class);
    },
    UserAwardRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserAwardRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserAward::class);
    },
    UserCharactersRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserCharactersRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserCharacters::class);
    },
    UserLayerRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserLayerRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserLayer::class);
    },
    UserLockRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserLockRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserLock::class);
    },
    UserRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(User::class);
    },
    UserIpTableRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserIpTableRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserIpTable::class);
    },
    UserInvitationRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserInvitationRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserInvitation::class);
    },
    UserMapRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserMapRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserMap::class);
    },
    UserProfileVisitorRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserProfileVisitorRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserProfileVisitor::class);
    },
    UserSettingRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserSettingRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserSetting::class);
    },
    UserTagRepositoryInterface::class => function (
        ContainerInterface $c
    ): UserTagRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(UserTag::class);
    },
    WeaponRepositoryInterface::class => function (
        ContainerInterface $c
    ): WeaponRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(Weapon::class);
    },
    WeaponShieldRepositoryInterface::class => function (
        ContainerInterface $c
    ): WeaponShieldRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(WeaponShield::class);
    },
    WormholeEntryRepositoryInterface::class => function (
        ContainerInterface $c
    ): WormholeEntryRepositoryInterface {
        return $c->get(EntityManagerInterface::class)->getRepository(WormholeEntry::class);
    },
];
