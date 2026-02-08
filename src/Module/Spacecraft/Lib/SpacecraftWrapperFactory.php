<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\FleetWrapper;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Reactor\ReactorWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Station\Lib\StationWrapper;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class SpacecraftWrapperFactory implements SpacecraftWrapperFactoryInterface
{
    public function __construct(
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly ReactorWrapperFactoryInterface $reactorWrapperFactory,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private readonly GameControllerInterface $game,
        private readonly SpacecraftStateChangerInterface $spacecraftStateChanger,
        private readonly RepairUtilInterface $repairUtil,
        private readonly StateIconAndTitle $stateIconAndTitle,
        private readonly SystemDataDeserializerInterface $systemDataDeserializer
    ) {}

    #[\Override]
    public function wrapSpacecraft(Spacecraft $spacecraft): SpacecraftWrapperInterface
    {
        if ($spacecraft instanceof Ship) {
            return $this->wrapShip($spacecraft);
        }

        if ($spacecraft instanceof Station) {
            return $this->wrapStation($spacecraft);
        }

        if ($spacecraft instanceof TholianWeb) {
            return $this->wrapTholianWeb($spacecraft);
        }

        throw new RuntimeException('unknown spacecraft type');
    }

    #[\Override]
    public function wrapStation(Station $station): StationWrapperInterface
    {
        return new StationWrapper(
            $station,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this,
            $this->reactorWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle,
            $this->colonyLibFactory
        );
    }

    #[\Override]
    public function wrapShip(Ship $ship): ShipWrapperInterface
    {
        return new ShipWrapper(
            $ship,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this,
            $this->reactorWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle,
            $this->colonyLibFactory
        );
    }

    private function wrapTholianWeb(TholianWeb $tholianWeb): TholianWebWrapper
    {
        return new TholianWebWrapper(
            $tholianWeb,
            $this->spacecraftSystemManager,
            $this->systemDataDeserializer,
            $this->torpedoTypeRepository,
            $this->game,
            $this,
            $this->reactorWrapperFactory,
            $this->spacecraftStateChanger,
            $this->repairUtil,
            $this->stateIconAndTitle,
            $this->colonyLibFactory
        );
    }

    #[\Override]
    public function wrapShips(array $ships): Collection
    {
        $result = new ArrayCollection();

        foreach ($ships as $key => $ship) {
            $result->set($key, $this->wrapShip($ship));
        }

        return $result;
    }

    #[\Override]
    public function wrapSpacecrafts(array $spacecrafts): Collection
    {
        return new ArrayCollection($spacecrafts)
            ->map(fn (Spacecraft $spacecraft): SpacecraftWrapperInterface => $this->wrapSpacecraft($spacecraft));
    }

    #[\Override]
    public function wrapSpacecraftsAsGroups(
        Collection $spacecrafts
    ): Collection {

        /** @var Collection<string, SpacecraftGroupInterface> */
        $groups = new ArrayCollection();

        foreach (SpacecraftGroup::sortSpacecraftCollection($spacecrafts) as $spacecraft) {
            $fleet = $spacecraft instanceof Ship ? $spacecraft->getFleet() : null;
            $fleetId = $fleet === null ? 0 : $fleet->getId();
            $sort = $fleet === null ? PHP_INT_MAX : $fleet->getSort();
            $groupKey = sprintf('%d_%d', $sort, $fleetId);

            if (!$groups->containsKey($groupKey)) {
                $groups->set($groupKey, new SpacecraftGroup(
                    $fleet === null ? 'Einzelschiffe' : $fleet->getName(),
                    $fleet === null ? null : $fleet->getUser()
                ));
            }
            /** @var SpacecraftGroupInterface */
            $group = $groups->get($groupKey);
            $group->addSpacecraftWrapper($this->wrapSpacecraft($spacecraft));
        }

        return $groups;
    }

    #[\Override]
    public function wrapFleet(Fleet $fleet): FleetWrapperInterface
    {
        return new FleetWrapper($fleet, $this, $this->game, false);
    }

    #[\Override]
    public function wrapFleets(array $fleets): array
    {
        return array_map(
            fn (Fleet $fleet): FleetWrapperInterface => $this->wrapFleet($fleet),
            $fleets
        );
    }
}
