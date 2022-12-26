<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShipWrapperFactory implements ShipWrapperFactoryInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CancelRepairInterface $cancelRepair,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper,
        ShipSystemDataFactoryInterface $shipSystemDataFactory
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->cancelRepair = $cancelRepair;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
        $this->shipSystemDataFactory = $shipSystemDataFactory;
    }

    public function wrapShip(ShipInterface $ship): ShipWrapperInterface
    {
        return new ShipWrapper(
            $ship,
            $this->shipSystemManager,
            $this->shipRepository,
            $this->colonyLibFactory,
            $this->cancelRepair,
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper,
            $this,
            $this->shipSystemDataFactory
        );
    }

    public function wrapShips(array $ships): array
    {
        $result = [];

        foreach ($ships as $key => $ship) {
            $result[$key] = $this->wrapShip($ship);
        }

        return $result;
    }

    public function wrapShipsAsFleet(array $ships, bool $isSingleShips = false): FleetWrapperInterface
    {
        $fleet = new Fleet();
        foreach ($ships as $key => $value) {
            $fleet->getShips()->set($key, $value);
        }

        if ($isSingleShips) {
            $fleet->setSort(PHP_INT_MAX);
            $fleet->setName(_('Einzelschiffe'));
        } else {
            $fleet->setName(current($ships)->getFleet()->getName());
            $fleet->setUser(current($ships)->getUser());
            $fleet->setSort(current($ships)->getFleet()->getSort());
        }

        return new FleetWrapper($fleet, $this, $this->game, $isSingleShips);
    }

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface
    {
        return new FleetWrapper($fleet, $this, $this->game, false);
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
