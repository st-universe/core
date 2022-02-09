<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartShuttle;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StartShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_SHUTTLE';

    private ShipRepositoryInterface $shipRepository;

    private ShipLoaderInterface $shipLoader;

    private ShipCreatorInterface $shipCreator;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipLoaderInterface $shipLoader,
        ShipCreatorInterface $shipCreator,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipCrewRepositoryInterface $shipCrewRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipLoader = $shipLoader;
        $this->shipCreator = $shipCreator;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $commodityId = request::postIntFatal('shid');

        $plan = $this->shipBuildplanRepository->getShuttleBuildplan($commodityId);

        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }
        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        // check if ship storage contains shuttle commodity
        $storage = $ship->getStorage();

        if (!$storage->containsKey($rump->getGoodId())) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                $this->commodityRepository->find((int) $rump->getGoodId())->getName()
            );
            return;
        }

        // check if ship has excess crew
        if ($ship->getCrewCount() - $ship->getBuildplan()->getCrew() < $plan->getCrew()) {
            $game->addInformation(sprintf(_('Es werden %d freie Crewman für den Start des %s benötigt'), $plan->getCrew(), $rump->getName()));
            return;
        }

        // check if ship got enough energy
        if ($ship->getEps() < $rump->getBaseEps()) {
            $game->addInformation(sprintf(_('Es wird %d Energie für den Start des %s benötigt'), $rump->getBaseEps(), $rump->getName()));
            return;
        }

        // remove shuttle from storage
        $this->shipStorageManager->lowerStorage(
            $ship,
            $rump->getCommodity(),
            1
        );

        // start shuttle and transfer crew
        $this->startShuttle($ship, $plan);

        $game->addInformation(sprintf(_('%s wurde erfolgreich gestartet'), $rump->getName()));
    }

    private function startShuttle(ShipInterface $ship, ShipBuildplanInterface $plan): void
    {
        $rump = $plan->getRump();

        $shuttle = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        );

        $this->entityManager->flush();

        //reload ship with systems
        $shuttle = $this->shipRepository->find($shuttle->getId());

        $shuttle->setEps($shuttle->getMaxEps());
        $shuttle->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);

        $shuttle->setMap($ship->getMap());
        $shuttle->setStarsystemMap($ship->getStarsystemMap());

        $shipCrewArray = $ship->getCrewlist()->getValues();
        for ($i = 0; $i < $plan->getCrew(); $i++) {
            $shipCrew = $shipCrewArray[$i];
            $shipCrew->setShip($shuttle);
            $ship->getCrewlist()->removeElement($shipCrew);

            $this->shipCrewRepository->save($shipCrew);
        }

        $this->shipRepository->save($shuttle);

        $ship->setEps($ship->getEps() - $shuttle->getMaxEps());
        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
