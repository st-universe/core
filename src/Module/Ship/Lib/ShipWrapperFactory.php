<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShipWrapperFactory implements ShipWrapperFactoryInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CancelRepairInterface $cancelRepair,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->cancelRepair = $cancelRepair;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
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
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper
        );
    }

    public function wrapShips(array $ships): array
    {
        array_walk($ships, function ($ship, &$key) {
            $key = $this->wrapShip($ship);
        });

        return $ships;
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

        return new FleetWrapper($fleet, $this, $this->game);
    }

    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface
    {
        return new FleetWrapper($fleet, $this, $this->game);
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
