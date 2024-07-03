<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use JBBCode\Parser;
use Override;
use RuntimeException;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipWrapperFactory implements ShipWrapperFactoryInterface
{
    public function __construct(
        private ShipSystemManagerInterface $shipSystemManager,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private GameControllerInterface $game,
        private ShipStateChangerInterface $shipStateChanger,
        private RepairUtilInterface $repairUtil,
        private UserRepositoryInterface $userRepository,
        private Parser $bbCodeParser,
        private SystemDataDeserializerInterface $systemDataDeserializer
    ) {
    }

    #[Override]
    public function wrapShip(ShipInterface $ship): ShipWrapperInterface
    {
        return new ShipWrapper(
            $ship,
            $this->shipSystemManager,
            $this->systemDataDeserializer,
            $this->colonyLibFactory,
            $this->torpedoTypeRepository,
            $this->game,
            $this,
            $this->shipStateChanger,
            $this->repairUtil,
            $this->bbCodeParser
        );
    }

    #[Override]
    public function wrapShips(array $ships): Collection
    {
        $result = new ArrayCollection();

        foreach ($ships as $key => $ship) {
            $result->set($key, $this->wrapShip($ship));
        }

        return $result;
    }

    #[Override]
    public function wrapShipsAsFleet(array $ships, bool $isSingleShips = false): FleetWrapperInterface
    {
        if ($ships === []) {
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

    #[Override]
    public function wrapFleet(FleetInterface $fleet): FleetWrapperInterface
    {
        return new FleetWrapper($fleet, $this, $this->game, false);
    }

    #[Override]
    public function wrapFleets(array $fleets): array
    {
        return array_map(
            fn (FleetInterface $fleet): FleetWrapperInterface => $this->wrapFleet($fleet),
            $fleets
        );
    }
}
