<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class AttackedBattleParty extends AbstractBattleParty
{
    #[\Override]
    protected function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        if ($fleetWrapper !== null) {

            // only uncloaked defenders fight
            $uncloakedDefenders = $fleetWrapper->getShipWrappers()
                ->filter(fn (SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isCloaked())
                ->toArray();

            $allDefenders = $this->addDockedTo($uncloakedDefenders);

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            return $allDefenders->isEmpty()
                ? $fleetWrapper->getShipWrappers()
                : $allDefenders;
        } else {

            return $this->addDockedTo([$this->leader->get()->getId() => $this->leader]);
        }
    }

    /**
     * @param array<int, covariant SpacecraftWrapperInterface> $spacecrafts
     *
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    private function addDockedTo(array $spacecrafts): Collection
    {
        $dockedToWrappers = [];

        foreach ($spacecrafts as $wrapper) {
            $dockedToWrapper = $wrapper instanceof ShipWrapperInterface ? $wrapper->getDockedToStationWrapper() : null;
            if (
                $dockedToWrapper === null
                || $dockedToWrapper->get()->getUser()->isNpc()
            ) {
                continue;
            }

            $dockedToWrappers[$dockedToWrapper->get()->getId()] = $dockedToWrapper;
        }

        return new ArrayCollection($spacecrafts + $dockedToWrappers);
    }
}
