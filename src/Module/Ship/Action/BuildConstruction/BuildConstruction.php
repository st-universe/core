<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BuildConstruction;

use BadMethodCallException;
use InvalidArgumentException;
use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\Dock\DockModeEnum;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactoryInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
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
        CommodityTypeConstants::COMMODITY_BUILDING_MATERIALS => 100,
        CommodityTypeConstants::COMMODITY_DURANIUM => 50
    ];

    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly ShipRepositoryInterface $shipRepository,
        private readonly StationRepositoryInterface $stationRepository,
        private readonly ShipLoaderInterface $shipLoader,
        private readonly ShipCreatorInterface $shipCreator,
        private readonly SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private readonly StorageManagerInterface $storageManager,
        private readonly SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private readonly ShipRumpUserRepositoryInterface $shipRumpUserRepository,
        private readonly CommodityRepositoryInterface $commodityRepository,
        private readonly DockingPrivilegeRepositoryInterface $dockingPrivilegeRepository,
        private readonly SpacecraftFactoryInterface $spacecraftFactory,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager
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
            $game->getInfo()->addInformation(_('In diesem Sektor kann keine Station errichtet werden'));
            return;
        }

        // check if ship in wormhole
        if ($ship->getSystem() !== null && $ship->getSystem()->isWormhole()) {
            $game->getInfo()->addInformation(_("In Wurmlöchern können keine Stationen errichtet werden"));
            return;
        }

        // check if there already is a base
        if ($this->stationRepository->getStationOnLocation($ship->getLocation()) !== null) {
            $game->getInfo()->addInformation("Hier ist bereits eine Station errichtet");
            return;
        }

        // check if the construction limit is reached
        $limit = SpacecraftRumpRoleEnum::CONSTRUCTION->getBuildLimit();
        if ($this->spacecraftRepository->getAmountByUserAndRump($userId, $rumpId) >= $limit) {
            $game->getInfo()->addInformation(sprintf(_('Es können nur %d Konstrukte errichtet werden'), $limit));
            return;
        }

        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
            $game->getInfo()->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->getInfo()->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->isCloaked()) {
            $game->getInfo()->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->getInfo()->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->isShielded()) {
            $game->getInfo()->addInformation(_("Die Schilde sind aktiviert"));
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
                $neededEps += $plan->getRump()->getBaseValues()->getBaseEps();
            }
        }

        // check if ship has excess crew
        if ($ship->getExcessCrewCount() < $neededCrew) {
            $game->getInfo()->addInformation(sprintf(
                _('Nicht genügend Crew für den Start der %d Workbees vorhanden, benötigt wird %d'),
                count($workbeePlans),
                $neededCrew
            ));
            return;
        }

        // check if ship got enough energy
        if ($epsSystem->getEps() < $neededEps) {
            $game->getInfo()->addInformation(sprintf(
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
            $game->getInfo()->addInformation(sprintf(_('%s wurde erfolgreich gestartet'), $rump->getName()));
        }

        // use build ressources
        foreach (self::NEEDED_RESOURCES as $key => $amount) {
            $commodity = $this->commodityRepository->find($key);

            if ($commodity === null) {
                throw new InvalidArgumentException('commodity not existent');
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

        $game->getInfo()->addInformation(sprintf(_('%s wurde erfolgreich errichtet'), $construction->getName()));
        $game->getInfo()->addInformation('Die gestarteten Workbees haben an das Konstrukt angedockt');
    }

    private function startWorkbee(Ship $ship, EpsSystemData $epsSystem, SpacecraftBuildplan $plan): Ship
    {
        $rump = $plan->getRump();

        $workbeeWrapper = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        )
            ->setLocation($ship->getLocation())
            ->loadEps(100)
            ->transferCrew($ship)
            ->finishConfiguration();

        $workbee = $workbeeWrapper->get();

        $this->spacecraftSystemManager->activate($workbeeWrapper, SpacecraftSystemTypeEnum::NBS, true);

        $workbeeEps = $workbeeWrapper->getEpsSystemData();

        if ($workbeeEps === null) {
            throw new BadMethodCallException('workbee has no eps system installed');
        }

        //lower ship eps
        $epsSystem->lowerEps($workbeeEps->getMaxEps())->update();

        $this->shipRepository->save($workbee);
        $this->shipRepository->save($ship);

        return $workbee;
    }

    private function buildConstruction(Ship $ship, int $rumpId): Station
    {
        $rump = $this->spacecraftRumpRepository->find($rumpId);

        if ($rump === null) {
            throw new InvalidArgumentException('rump does not exist');
        }

        $construction = $this->spacecraftFactory->create($rump);
        if (!$construction instanceof Station) {
            throw new BadMethodCallException(sprintf('rumpId %d is not a station', $rumpId));
        }

        $baseHull = $rump->getBaseValues()->getBaseHull();

        $construction->setUser($ship->getUser());
        $construction->setRump($rump);
        $construction->setName($rump->getName());
        $construction->setMaxHuell($baseHull);
        $construction->getCondition()->setHull($baseHull);
        $construction->getCondition()->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);

        $construction->setLocation($ship->getLocation());

        $this->spacecraftRepository->save($construction);

        return $construction;
    }

    private function allowDockingForOwner(Station $station): void
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
