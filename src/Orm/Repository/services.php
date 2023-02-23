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
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\ColonyClassDeposit;
use Stu\Orm\Entity\ColonyClassResearch;
use Stu\Orm\Entity\ColonyDepositMining;
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
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\IgnoreList;
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
use Stu\Orm\Entity\UserIpTable;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Entity\UserMap;
use Stu\Orm\Entity\UserProfileVisitor;
use Stu\Orm\Entity\Weapon;
use Stu\Orm\Entity\WormholeEntry;

return [
    AllianceRepositoryInterface::class => static fn (ContainerInterface $c): AllianceRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Alliance::class),
    AllianceBoardRepositoryInterface::class => static fn (ContainerInterface $c): AllianceBoardRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoard::class),
    AllianceBoardPostRepositoryInterface::class => static fn (ContainerInterface $c): AllianceBoardPostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardPost::class),
    AllianceBoardTopicRepositoryInterface::class => static fn (ContainerInterface $c): AllianceBoardTopicRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AllianceBoardTopic::class),
    AllianceJobRepositoryInterface::class => static fn (ContainerInterface $c): AllianceJobRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AllianceJob::class),
    AllianceRelationRepositoryInterface::class => static fn (ContainerInterface $c): AllianceRelationRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AllianceRelation::class),
    AwardRepositoryInterface::class => static fn (ContainerInterface $c): AwardRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Award::class),
    BasicTradeRepositoryInterface::class => static fn (ContainerInterface $c): BasicTradeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BasicTrade::class),
    BlockedUserRepositoryInterface::class => static fn (ContainerInterface $c): BlockedUserRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BlockedUser::class),
    BuildingRepositoryInterface::class => static fn (ContainerInterface $c): BuildingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Building::class),
    BuildingCostRepositoryInterface::class => static fn (ContainerInterface $c): BuildingCostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingCost::class),
    BuildingFieldAlternativeRepositoryInterface::class => static fn (ContainerInterface $c): BuildingFieldAlternativeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingFieldAlternative::class),
    BuildingFunctionRepositoryInterface::class => static fn (ContainerInterface $c): BuildingFunctionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingFunction::class),
    BuildingCommodityRepositoryInterface::class => static fn (ContainerInterface $c): BuildingCommodityRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingCommodity::class),
    BuildingUpgradeRepositoryInterface::class => static fn (ContainerInterface $c): BuildingUpgradeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgrade::class),
    BuildingUpgradeCostRepositoryInterface::class => static fn (ContainerInterface $c): BuildingUpgradeCostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildingUpgradeCost::class),
    BuildplanHangarRepositoryInterface::class => static fn (ContainerInterface $c): BuildplanHangarRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildplanHangar::class),
    BuildplanModuleRepositoryInterface::class => static fn (ContainerInterface $c): BuildplanModuleRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(BuildplanModule::class),
    ColonyRepositoryInterface::class => static fn (ContainerInterface $c): ColonyRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Colony::class),
    ColonyTerraformingRepositoryInterface::class => static fn (ContainerInterface $c): ColonyTerraformingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyTerraforming::class),
    CommodityRepositoryInterface::class => static fn (ContainerInterface $c): CommodityRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Commodity::class),
    ConstructionProgressRepositoryInterface::class => static fn (ContainerInterface $c): ConstructionProgressRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgress::class),
    ConstructionProgressModuleRepositoryInterface::class => static fn (ContainerInterface $c): ConstructionProgressModuleRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ConstructionProgressModule::class),
    ContactRepositoryInterface::class => static fn (ContainerInterface $c): ContactRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Contact::class),
    ColonyClassDepositRepositoryInterface::class => static fn (ContainerInterface $c): ColonyClassDepositRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyClassDeposit::class),
    ColonyDepositMiningRepositoryInterface::class => static fn (ContainerInterface $c): ColonyDepositMiningRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyDepositMining::class),
    ColonyShipRepairRepositoryInterface::class => static fn (ContainerInterface $c): ColonyShipRepairRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyShipRepair::class),
    ColonyShipQueueRepositoryInterface::class => static fn (ContainerInterface $c): ColonyShipQueueRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyShipQueue::class),
    CrewRaceRepositoryInterface::class => static fn (ContainerInterface $c): CrewRaceRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(CrewRace::class),
    CrewRepositoryInterface::class => static fn (ContainerInterface $c): CrewRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Crew::class),
    CrewTrainingRepositoryInterface::class => static fn (ContainerInterface $c): CrewTrainingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(CrewTraining::class),
    DatabaseCategoryRepositoryInterface::class => static fn (ContainerInterface $c): DatabaseCategoryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(DatabaseCategory::class),
    DatabaseEntryRepositoryInterface::class => static fn (ContainerInterface $c): DatabaseEntryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(DatabaseEntry::class),
    DatabaseTypeRepositoryInterface::class => static fn (ContainerInterface $c): DatabaseTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(DatabaseType::class),
    DatabaseUserRepositoryInterface::class => static fn (ContainerInterface $c): DatabaseUserRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(DatabaseUser::class),
    DealsRepositoryInterface::class => static fn (ContainerInterface $c): DealsRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Deals::class),
    AuctionBidRepositoryInterface::class => static fn (ContainerInterface $c): AuctionBidRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AuctionBid::class),
    DockingPrivilegeRepositoryInterface::class => static fn (ContainerInterface $c): DockingPrivilegeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(DockingPrivilege::class),
    FactionRepositoryInterface::class => static fn (ContainerInterface $c): FactionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Faction::class),
    FleetRepositoryInterface::class => static fn (ContainerInterface $c): FleetRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Fleet::class),
    FlightSignatureRepositoryInterface::class => static fn (ContainerInterface $c): FlightSignatureRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(FlightSignature::class),
    AstroEntryRepositoryInterface::class => static fn (ContainerInterface $c): AstroEntryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(AstronomicalEntry::class),
    GameConfigRepositoryInterface::class => static fn (ContainerInterface $c): GameConfigRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(GameConfig::class),
    GameTurnRepositoryInterface::class => static fn (ContainerInterface $c): GameTurnRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(GameTurn::class),
    GameTurnStatsRepositoryInterface::class => static fn (ContainerInterface $c): GameTurnStatsRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(GameTurnStats::class),
    HistoryRepositoryInterface::class => static fn (ContainerInterface $c): HistoryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(History::class),
    IgnoreListRepositoryInterface::class => static fn (ContainerInterface $c): IgnoreListRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(IgnoreList::class),
    KnCommentRepositoryInterface::class => static fn (ContainerInterface $c): KnCommentRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(KnComment::class),
    KnPostRepositoryInterface::class => static fn (ContainerInterface $c): KnPostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(KnPost::class),
    KnPostToPlotApplicationRepositoryInterface::class => static fn (ContainerInterface $c): KnPostToPlotApplicationRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(KnPostToPlotApplication::class),
    LayerRepositoryInterface::class => static fn (ContainerInterface $c): LayerRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Layer::class),
    LotteryTicketRepositoryInterface::class => static fn (ContainerInterface $c): LotteryTicketRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(LotteryTicket::class),
    MapBorderTypeRepositoryInterface::class => static fn (ContainerInterface $c): MapBorderTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(MapBorderType::class),
    MapFieldTypeRepositoryInterface::class => static fn (ContainerInterface $c): MapFieldTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(MapFieldType::class),
    MapRegionRepositoryInterface::class => static fn (ContainerInterface $c): MapRegionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(MapRegion::class),
    MapRepositoryInterface::class => static fn (ContainerInterface $c): MapRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Map::class),
    ModuleBuildingFunctionRepositoryInterface::class => static fn (ContainerInterface $c): ModuleBuildingFunctionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ModuleBuildingFunction::class),
    ModuleCostRepositoryInterface::class => static fn (ContainerInterface $c): ModuleCostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ModuleCost::class),
    ModuleRepositoryInterface::class => static fn (ContainerInterface $c): ModuleRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Module::class),
    ModuleQueueRepositoryInterface::class => static fn (ContainerInterface $c): ModuleQueueRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ModuleQueue::class),
    ModuleSpecialRepositoryInterface::class => static fn (ContainerInterface $c): ModuleSpecialRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ModuleSpecial::class),
    NewsRepositoryInterface::class => static fn (ContainerInterface $c): NewsRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(News::class),
    NoteRepositoryInterface::class => static fn (ContainerInterface $c): NoteRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Note::class),
    PlanetFieldRepositoryInterface::class => static fn (ContainerInterface $c): PlanetFieldRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PlanetField::class),
    PlanetFieldTypeBuildingRepositoryInterface::class => static fn (ContainerInterface $c): PlanetFieldTypeBuildingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldTypeBuilding::class),
    PlanetFieldTypeRepositoryInterface::class => static fn (ContainerInterface $c): PlanetFieldTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PlanetFieldType::class),
    ColonyClassRepositoryInterface::class => static fn (ContainerInterface $c): ColonyClassRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyClass::class),
    ColonyClassResearchRepositoryInterface::class => static fn (ContainerInterface $c): ColonyClassResearchRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ColonyClassResearch::class),
    PrestigeLogRepositoryInterface::class => static fn (ContainerInterface $c): PrestigeLogRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PrestigeLog::class),
    PrivateMessageRepositoryInterface::class => static fn (ContainerInterface $c): PrivateMessageRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PrivateMessage::class),
    PrivateMessageFolderRepositoryInterface::class => static fn (ContainerInterface $c): PrivateMessageFolderRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(PrivateMessageFolder::class),
    RepairTaskRepositoryInterface::class => static fn (ContainerInterface $c): RepairTaskRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(RepairTask::class),
    ResearchRepositoryInterface::class => static fn (ContainerInterface $c): ResearchRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Research::class),
    ResearchedRepositoryInterface::class => static fn (ContainerInterface $c): ResearchedRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Researched::class),
    ResearchDependencyRepositoryInterface::class => static fn (ContainerInterface $c): ResearchDependencyRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ResearchDependency::class),
    RpgPlotRepositoryInterface::class => static fn (ContainerInterface $c): RpgPlotRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(RpgPlot::class),
    RpgPlotMemberRepositoryInterface::class => static fn (ContainerInterface $c): RpgPlotMemberRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(RpgPlotMember::class),
    SessionStringRepositoryInterface::class => static fn (ContainerInterface $c): SessionStringRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(SessionString::class),
    ShipBuildplanRepositoryInterface::class => static fn (ContainerInterface $c): ShipBuildplanRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipBuildplan::class),
    ShipCrewRepositoryInterface::class => static fn (ContainerInterface $c): ShipCrewRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipCrew::class),
    ShipLogRepositoryInterface::class => static fn (ContainerInterface $c): ShipLogRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipLog::class),
    ShipRepositoryInterface::class => static fn (ContainerInterface $c): ShipRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Ship::class),
    ShipRumpBuildingFunctionRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpBuildingFunctionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpBuildingFunction::class),
    ShipRumpCategoryRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpCategoryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategory::class),
    ShipRumpCategoryRoleCrewRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpCategoryRoleCrewRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCategoryRoleCrew::class),
    ShipRumpColonizationBuildingRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpColonizationBuildingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpColonizationBuilding::class),
    ShipRumpCostRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpCostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpCost::class),
    ShipRumpModuleLevelRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpModuleLevelRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpModuleLevel::class),
    ShipRumpRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRump::class),
    ShipRumpRoleRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpRoleRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpRole::class),
    ShipRumpSpecialRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpSpecialRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpSpecial::class),
    ShipRumpUserRepositoryInterface::class => static fn (ContainerInterface $c): ShipRumpUserRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipRumpUser::class),
    ShipSystemRepositoryInterface::class => static fn (ContainerInterface $c): ShipSystemRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipSystem::class),
    ShipyardShipQueueRepositoryInterface::class => static fn (ContainerInterface $c): ShipyardShipQueueRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(ShipyardShipQueue::class),
    SpacecraftEmergencyRepositoryInterface::class => static fn (ContainerInterface $c): SpacecraftEmergencyRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(SpacecraftEmergency::class),
    StarSystemMapRepositoryInterface::class => static fn (ContainerInterface $c): StarSystemMapRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(StarSystemMap::class),
    StarSystemRepositoryInterface::class => static fn (ContainerInterface $c): StarSystemRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(StarSystem::class),
    StarSystemTypeRepositoryInterface::class => static fn (ContainerInterface $c): StarSystemTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(StarSystemType::class),
    StationShipRepairRepositoryInterface::class => static fn (ContainerInterface $c): StationShipRepairRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(StationShipRepair::class),
    StorageRepositoryInterface::class => static fn (ContainerInterface $c): StorageRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Storage::class),
    TerraformingRepositoryInterface::class => static fn (ContainerInterface $c): TerraformingRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Terraforming::class),
    TachyonScanRepositoryInterface::class => static fn (ContainerInterface $c): TachyonScanRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TachyonScan::class),
    TerraformingCostRepositoryInterface::class => static fn (ContainerInterface $c): TerraformingCostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TerraformingCost::class),
    TholianWebRepositoryInterface::class => static fn (ContainerInterface $c): TholianWebRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TholianWeb::class),
    TorpedoTypeRepositoryInterface::class => static fn (ContainerInterface $c): TorpedoTypeRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TorpedoType::class),
    TorpedoStorageRepositoryInterface::class => static fn (ContainerInterface $c): TorpedoStorageRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TorpedoStorage::class),
    TradeLicenseInfoRepositoryInterface::class => static fn (ContainerInterface $c): TradeLicenseInfoRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeLicenseInfo::class),
    TradeLicenseRepositoryInterface::class => static fn (ContainerInterface $c): TradeLicenseRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeLicense::class),
    TradeOfferRepositoryInterface::class => static fn (ContainerInterface $c): TradeOfferRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeOffer::class),
    TradePostRepositoryInterface::class => static fn (ContainerInterface $c): TradePostRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradePost::class),
    TradeShoutboxRepositoryInterface::class => static fn (ContainerInterface $c): TradeShoutboxRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeShoutbox::class),
    TradeTransactionRepositoryInterface::class => static fn (ContainerInterface $c): TradeTransactionRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeTransaction::class),
    TradeTransferRepositoryInterface::class => static fn (ContainerInterface $c): TradeTransferRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(TradeTransfer::class),
    UserAwardRepositoryInterface::class => static fn (ContainerInterface $c): UserAwardRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserAward::class),
    UserLayerRepositoryInterface::class => static fn (ContainerInterface $c): UserLayerRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserLayer::class),
    UserLockRepositoryInterface::class => static fn (ContainerInterface $c): UserLockRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserLock::class),
    UserRepositoryInterface::class => static fn (ContainerInterface $c): UserRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(User::class),
    UserIpTableRepositoryInterface::class => static fn (ContainerInterface $c): UserIpTableRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserIpTable::class),
    UserMapRepositoryInterface::class => static fn (ContainerInterface $c): UserMapRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserMap::class),
    UserProfileVisitorRepositoryInterface::class => static fn (ContainerInterface $c): UserProfileVisitorRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(UserProfileVisitor::class),
    WeaponRepositoryInterface::class => static fn (ContainerInterface $c): WeaponRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(Weapon::class),
    WormholeEntryRepositoryInterface::class => static fn (ContainerInterface $c): WormholeEntryRepositoryInterface => $c->get(EntityManagerInterface::class)->getRepository(WormholeEntry::class),
];
