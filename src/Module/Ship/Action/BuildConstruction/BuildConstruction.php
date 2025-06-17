<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BuildConstruction;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Component\Station\StationEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\DockingPrivilegeRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\ShipRumpUserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class BuildConstruction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_CONSTRUCTION';

    public const int NEEDED_WORKBEES = 5;

    public const array NEEDED_RESOURCES = [
        CommodityTypeEnum::COMMODITY_BUILDING_MATERIALS => 100,
        CommodityTypeEnum::COMMODITY_DURANIUM => 50
    ];

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ShipRepositoryInterface $shipRepository,
        private StationRepositoryInterface $stationRepository,
        private ShipLoaderInterface $shipLoader,
        private ShipCreatorInterface $shipCreator,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private StorageManagerInterface $storageManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        if (!$ship->canBuildConstruction()) {
            return;
        }

        $rumpId = $ship->getUser()->getFactionId() + SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_CONSTRUCTION;
        if (!$this->shipRumpUserRepository->isAvailableForUser($rumpId, $userId)) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->getLocation()->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_STATION_CONSTRUCTION)) {
            $game->addInformation(_('In diesem Sektor kann keine Station errichtet werden'));
            return;
        }

        // check if ship in wormhole
        if ($ship->getSystem() !== null && $ship->getSystem()->isWormhole()) {
            $game->addInformation(_("In Wurmlöchern können keine Stationen errichtet werden"));
            return;
        }

        // check if there already is a base
        if ($this->stationRepository->getStationOnLocation($ship->getLocation()) !== null) {
            $game->addInformation("Hier ist bereits eine Station errichtet");
            return;
        }

        // check if the construction limit is reached
        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[SpacecraftRumpEnum::SHIP_ROLE_CONSTRUCTION];
        if ($this->spacecraftRepository->getAmountByUserAndRump($userId, $rumpId) >= $limit) {
            $game->addInformation(sprintf(_('Es können nur %d Konstrukte errichtet werden'), $limit));
            return;
        }

        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->isCloaked()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->isShielded()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        //calculate needed eps and crew
        $workbeePlans = [];
        $neededCrew = 0;
        $neededEps = 0;
        foreach ($ship->getStorage() as $stor) {
            if (count($workbeePlans) === self::NEEDED_WORKBEES) {
                break;
            }

            $commodity = $stor->getCommodity();

            if (!$commodity->isWorkbee()) {
                continue;
            }

            $plan = $this->spacecraftBuildplanRepository->getShuttleBuildplan($commodity->getId());
            if ($plan === null) {
                continue;
            }

            $amount = $stor->getAmount();
            $stillNeeded = self::NEEDED_WORKBEES - count($workbeePlans);

            for ($i = 0; $i < min($amount, $stillNeeded); $i++) {
                $workbeePlans[] = $plan;
                $neededCrew += $plan->getCrew();
                $neededEps += $plan->getRump()->getBaseEps();
            }
        }

        // check if ship has excess crew
        if ($ship->getExcessCrewCount() < $neededCrew) {
            $game->addInformation(sprintf(
                _('Nicht genügend Crew für den Start der %d Workbees vorhanden, benötigt wird %d'),
                count($workbeePlans),
                $neededCrew
            ));
            return;
        }

        // check if ship got enough energy
        if ($epsSystem->getEps() < $neededEps) {
            $game->addInformation(sprintf(
                _('Es wird insgesamt %d Energie für den Start der %d Workbees benötigt'),
                $neededEps,
                count($workbeePlans)
            ));
            return;
        }

        // start workbees
        $workbees = [];
        foreach ($workbeePlans as $plan) {
            $rump = $plan->getRump();

            $rumpCommodity = $rump->getCommodity();
            if ($rumpCommodity !== null) {
                // remove shuttle from storage
                $this->storageManager->lowerStorage(
                    $ship,
                    $rumpCommodity,
                    1
                );
            }

            // start workbee and transfer crew
            $workbees[] = $this->startWorkbee($ship, $epsSystem, $plan);
            $game->addInformation(sprintf(_('%s wurde erfolgreich gestartet'), $rump->getName()));
        }

        // use build ressources
        foreach (self::NEEDED_RESOURCES as $key => $amount) {
            $commodity = $this->commodityRepository->find($key);

            if ($commodity === null) {
                throw new RuntimeException('commodity not existent');
            }

            $this->storageManager->lowerStorage(
                $ship,
                $commodity,
                $amount
            );
        }

        // build construction
        $construction = $this->buildConstruction($ship, $rumpId);

        // allow docking for owner
        $this->allowDockingForOwner($construction);

        // dock workbees to construction
        foreach ($workbees as $workbee) {
            $workbee->setDockedTo($construction);
            $this->shipRepository->save($workbee);
        }

        $game->addInformation(sprintf(_('%s wurde erfolgreich errichtet'), $construction->getName()));
        $game->addInformation('Die gestarteten Workbees haben an das Konstrukt angedockt');
    }

    private function startWorkbee(ShipInterface $ship, EpsSystemData $epsSystem, SpacecraftBuildplanInterface $plan): ShipInterface
    {
        $rump = $plan->getRump();

        $workbeeWrapper = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        )
            ->setLocation($ship->getLocation())
            ->loadEps(100)
            ->finishConfiguration();

        $workbee = $workbeeWrapper->get();

        $workbeeEps = $workbeeWrapper->getEpsSystemData();

        if ($workbeeEps === null) {
            throw new RuntimeException('workbee has not eps system installed');
        }

        $workbee->getSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)->setMode(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);
        $workbee->getSpacecraftSystem(SpacecraftSystemTypeEnum::NBS)->setMode(SpacecraftSystemModeEnum::MODE_ON);

        $shipCrewArray = $ship->getCrewAssignments()->getValues();
        for ($i = 0; $i < $plan->getCrew(); $i++) {
            $this->troopTransferUtility->assignCrew($shipCrewArray[$i], $workbee);
        }

        //lower ship eps
        $epsSystem->lowerEps($workbeeEps->getMaxEps())->update();

        $this->shipRepository->save($workbee);
        $this->shipRepository->save($ship);

        return $workbee;
    }

    private function buildConstruction(ShipInterface $ship, int $rumpId): StationInterface
    {
        $rump = $this->spacecraftRumpRepository->find($rumpId);

        if ($rump === null) {
            throw new RuntimeException('rump does not exist');
        }

        $construction = $this->stationRepository->prototype();
        $construction->setUser($ship->getUser());
        $construction->setRump($rump);
        $construction->setName($rump->getName());
        $construction->setHuell($rump->getBaseHull());
        $construction->setMaxHuell($rump->getBaseHull());
        $construction->setAlertStateGreen();
        $construction->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);

        $construction->setLocation($ship->getLocation());

        $this->stationRepository->save($construction);

        return $construction;
    }

    private function allowDockingForOwner(StationInterface $station): void
    {
        $dock = $this->dockingPrivilegeRepository->prototype();
        $dock->setPrivilegeMode(DockModeEnum::ALLOW);
        $dock->setPrivilegeType(DockTypeEnum::USER);
        $dock->setTargetId($station->getUser()->getId());
        $dock->setStation($station);

        $this->dockingPrivilegeRepository->save($dock);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
