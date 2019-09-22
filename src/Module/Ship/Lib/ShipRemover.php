<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use ShipData;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShipRemover implements ShipRemoverInterface
{
    private $shipSystemRepository;

    private $shipStorageRepository;

    private $shipCrewRepository;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipStorageRepositoryInterface $shipStorageRepository,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipStorageRepository = $shipStorageRepository;
        $this->shipCrewRepository = $shipCrewRepository;
    }

    public function destroy(ShipData $ship): void
    {
        $ship->deactivateSystems();
        $ship->changeFleetLeader();
        $ship->setFormerRumpsId($ship->getRumpId());
        $ship->setRumpId(TRUMFIELD_CLASS);
        $ship->setHuell(round($ship->getMaxHuell()/20));
        $ship->setUserId(USER_NOONE);
        $ship->setBuildplanId(0);
        $ship->setShield(0);
        $ship->setEps(0);
        $ship->setFleetId(0);
        $ship->setAlertState(1);
        $ship->setWarpState(0);
        $ship->setDock(0);
        $ship->setName(_('Trümmer'));
        $ship->setIsDestroyed(1);
        $ship->cancelRepair();

        $this->shipSystemRepository->truncateByShip((int) $ship->getId());
        // @todo Torpedos löschen

        $ship->save();

        $ship->clearCache();
    }

    public function remove(ShipData $ship): void
    {
        $ship->changeFleetLeader();
        $ship->deactivateTraktorBeam();

        $this->shipStorageRepository->truncateForShip((int) $ship->getId());
        $this->shipCrewRepository->truncateByShip((int) $ship->getId());
        $this->shipSystemRepository->truncateByShip((int) $ship->getId());

        $ship->deleteFromDatabase();
    }
}