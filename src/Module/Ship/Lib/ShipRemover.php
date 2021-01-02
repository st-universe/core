<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipRemover implements ShipRemoverInterface
{
    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipStorageRepositoryInterface $shipStorageRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipLeaverInterface $shipLeaver;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipStorageRepositoryInterface $shipStorageRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipLeaverInterface $shipLeaver
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipStorageRepository = $shipStorageRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipLeaver = $shipLeaver;
    }

    public function destroy(ShipInterface $ship): ?string
    {
        $msg = null;

        $this->shipSystemManager->deactivateAll($ship);

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        }

        //leave ship if there is crew
        if ($ship->getCrewCount() > 0)
        {
            $msg = $this->shipLeaver->leave($ship);
        }

        $ship->setFormerRumpId($ship->getRump()->getId());
        $ship->setRump($this->shipRumpRepository->find(ShipEnum::TRUMFIELD_CLASS));
        $ship->setHuell((int) round($ship->getMaxHuell()/20));
        $ship->setUser($this->userRepository->find(GameEnum::USER_NOONE));
        $ship->setBuildplan(null);
        $ship->setShield(0);
        $ship->setEps(0);
        $ship->setAlertState(1);
        $ship->setDockedTo(null);
        $ship->setName(_('Trümmer'));
        $ship->setIsDestroyed(true);
        $ship->setFleet(null);
        $ship->cancelRepair();

        $this->shipSystemRepository->truncateByShip((int) $ship->getId());
        // @todo Torpedos löschen

        $this->shipRepository->save($ship);

        return $msg;
    }

    public function remove(ShipInterface $ship): void
    {
        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        }
        $ship->deactivateTraktorBeam();

        foreach ($ship->getStorage() as $item) {
            $this->shipStorageRepository->delete($item);
        }
        $this->shipCrewRepository->truncateByShip((int) $ship->getId());
        $this->shipSystemRepository->truncateByShip((int) $ship->getId());

        $this->shipRepository->delete($ship);
    }

    private function changeFleetLeader(ShipInterface $obj): void
    {
        $ship = current(
            array_filter(
                $obj->getFleet()->getShips()->toArray(),
                function (ShipInterface $ship) use ($obj): bool {
                    return $ship !== $obj;
                }
            )
        );

        $fleet = $obj->getFleet();

        $obj->setFleet(null);
        $fleet->getShips()->removeElement($obj);

        $this->shipRepository->save($obj);

        if (!$ship) {
            $this->fleetRepository->delete($fleet);

            return;
        }
        $fleet->setLeadShip($ship);

        $this->fleetRepository->save($fleet);
    }
}
