<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StoreShuttle;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StoreShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_STORE_SHUTTLE';

    private ShipRepositoryInterface $shipRepository;

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private EntityManagerInterface $entityManager;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ShipRemoverInterface $shipRemover;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipLoaderInterface $shipLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipCrewRepositoryInterface $shipCrewRepository,
        EntityManagerInterface $entityManager,
        TroopTransferUtilityInterface $troopTransferUtility,
        ShipRemoverInterface $shipRemover
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipLoader = $shipLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->entityManager = $entityManager;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->shipRemover = $shipRemover;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipRepository->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        if (!$this->positionChecker->checkPosition($ship, $target)) {
            return;
        }
        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        if ($ship->getWarpState()) {
            $game->addInformation(_("Das Schiff hat den Warpantrieb aktiviert"));
            return;
        }

        if ($target->getShieldState()) {
            $game->addInformation(_("Das Schiff hat die Schilde aktiviert"));
            return;
        }

        // check if ship got shuttle ramp
        if (!$ship->hasShuttleRamp()) {
            $game->addInformation(_("Das Schiff verfügt über keine Shuttle-Rampe"));
            return;
        }

        // check if shuttle ramp is healthy
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }

        // check if shuttle slot available
        if (!$ship->hasFreeShuttleSpace()) {
            $game->addInformation(_("Die Shuttle-Rampe ist belegt"));
            return;
        }

        // check if troop quarter free
        if ($this->troopTransferUtility->getFreeQuarters($ship) < $target->getCrewCount()) {
            $game->addInformation(_('Nicht genügend Crew-Quartiere frei'));
            return;
        }

        // send shuttle to target storage
        $this->shipStorageManager->upperStorage(
            $ship,
            $target->getRump()->getCommodity(),
            1
        );

        // land workbee and transfer crew
        $this->storeWorkbee($ship, $target);

        $game->addInformation("Shuttle erfolgreich eingesammelt");
    }

    private function storeWorkbee(ShipInterface $ship, ShipInterface $target): void
    {
        foreach ($target->getCrewlist() as $shipCrew) {
            $shipCrew->setShip($ship);
            $this->shipCrewRepository->save($shipCrew);
        }
        $target->getCrewlist()->clear();
        $this->entityManager->flush();

        $this->shipRemover->remove($target);

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
