<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use InvalidArgumentException;
use JBBCode\Parser;
use JsonMapper\JsonMapperInterface;
use RuntimeException;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipWrapperFactory implements ShipWrapperFactoryInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private ShipStateChangerInterface $shipStateChanger;

    private RepairUtilInterface $repairUtil;

    private UserRepositoryInterface $userRepository;

    private Parser $bbCodeParser;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper,
        ShipSystemDataFactoryInterface $shipSystemDataFactory,
        ShipStateChangerInterface $shipStateChanger,
        RepairUtilInterface $repairUtil,
        UserRepositoryInterface $userRepository,
        Parser $bbCodeParser
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
        $this->shipSystemDataFactory = $shipSystemDataFactory;
        $this->shipStateChanger = $shipStateChanger;
        $this->repairUtil = $repairUtil;
        $this->userRepository = $userRepository;
        $this->bbCodeParser = $bbCodeParser;
    }

    public function wrapShip(ShipInterface $ship): ShipWrapperInterface
    {
        return new ShipWrapper(
            $ship,
            $this->shipSystemManager,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this->jsonMapper,
            $this,
            $this->shipSystemDataFactory,
            $this->shipStateChanger,
            $this->repairUtil,
            $this->bbCodeParser
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
        if (empty($ships)) {
            throw new InvalidArgumentException('ship array should not be empty');
        }

        $fleet = new Fleet();
        foreach ($ships as $key => $value) {
            $fleet->getShips()->set($key, $value);
        }

        if ($isSingleShips) {
            $fleet->setName(_('Einzelschiffe'));
            $fleet->setUser($this->userRepository->getFallbackUser());
            $fleet->setSort(PHP_INT_MAX);
        } else {
            $originalFleet = current($ships)->getFleet();
            if ($originalFleet === null) {
                throw new RuntimeException('ship should have fleet');
            }

            $fleet->setName($originalFleet->getName());
            $fleet->setUser(current($ships)->getUser());
            $fleet->setSort($originalFleet->getSort());
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
            fn (FleetInterface $fleet): FleetWrapperInterface => $this->wrapFleet($fleet),
            $fleets
        );
    }
}
