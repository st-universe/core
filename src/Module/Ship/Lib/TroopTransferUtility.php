<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TroopTransferUtility implements TroopTransferUtilityInterface
{
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;
    
    private CrewRepositoryInterface $crewRepository;

    //TODO clean up
    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CrewRepositoryInterface $crewRepository
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->crewRepository = $crewRepository;
    }

    public function getFreeQuarters(ShipInterface $ship): int
    {
        $free = $ship->getBuildplan()->getCrew() - $ship->getCrewCount();

        if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS))
        {
            $free += 100;
        }

        return max(0, $free);
    }

    public function getBeamableTroopCount(ShipInterface $ship): int
    {
        $max = $ship->getCrewCount() - $ship->getBuildplan()->getCrew();

        return max(0, $max);
    }
}
