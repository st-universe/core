<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class ShipWrapperFactory implements ShipWrapperFactoryInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CancelRepairInterface $cancelRepair,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->cancelRepair = $cancelRepair;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
    }

    public function wrapShip(ShipInterface $ship): ShipWrapperInterface
    {
        return new ShipWrapper(
            $ship,
            $this->shipSystemManager,
            $this->shipRepository,
            $this->shipSystemRepository,
            $this->colonyLibFactory,
            $this->cancelRepair,
            $this->game,
            $this->jsonMapper
        );
    }

    public function wrapShips(array $ships): array
    {
        return array_map(
            function (ShipInterface $ship): ShipWrapperInterface {
                return $this->wrapShip($ship);
            },
            $ships
        );
    }

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface
    {
        return new FleetWrapper($fleet, $this);
    }

    public function wrapFleets(array $fleets): array
    {
        return array_map(
            function (FleetInterface $fleet): FleetWrapperInterface {
                return $this->wrapFleet($fleet);
            },
            $fleets
        );
    }
}
