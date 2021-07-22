<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartWorkbee;

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

final class StartWorkbee implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_WORKBEE';

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

        $plan = $this->shipBuildplanRepository->getWorkbeeBuildplan($game->getUser()->getFaction()->getId());

        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        //TODO check if user has researched workbees

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

        // check if ship storage contains workbee commodity
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
        if ($ship->getCrewCount() - $ship->getBuildplan()->getCrew() < 1) {
            $game->addInformation(_('Es wird 1 freier Crewman benötigt um den Workbee zu starten'));
            return;
        }

        // remove workbee from storage
        $this->shipStorageManager->lowerStorage(
            $ship,
            $rump->getCommodity(),
            1
        );

        // start workbee and transfer crew
        $this->startWorkbee($ship, $plan);

        $game->addInformation("Workbee wurde erfolgreich gestartet");
    }

    private function startWorkbee(ShipInterface $ship, ShipBuildplanInterface $plan): void
    {
        $rump = $plan->getRump();

        $workbee = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        );

        $this->entityManager->flush();

        //reload ship with systems
        $workbee = $this->shipRepository->find($workbee->getId());

        $workbee->setEps($workbee->getMaxEps());
        $workbee->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);

        $workbee->setMap($ship->getMap());
        $workbee->setStarsystemMap($ship->getStarsystemMap());

        $shipCrew = current($ship->getCrewlist()->getValues());
        $shipCrew->setShip($workbee);
        $this->shipCrewRepository->save($shipCrew);

        $this->shipRepository->save($workbee);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
