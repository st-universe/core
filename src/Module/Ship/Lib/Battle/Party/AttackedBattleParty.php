<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class AttackedBattleParty extends AbstractBattleParty
{
    public function __construct(
        ShipWrapperInterface $attackedWrapper
    ) {
        parent::__construct(
            $attackedWrapper
        );
    }

    protected function initMembers(): Collection
    {
        $fleetWrapper = $this->leader->getFleetWrapper();

        if ($fleetWrapper !== null) {

            $spacecrafts = $fleetWrapper->getShipWrappers();

            $this->addDockedTo($spacecrafts);

            // only uncloaked defenders fight
            $uncloakedDefenders = $spacecrafts
                ->filter(fn (ShipWrapperInterface $wrapper) => !$wrapper->get()->getCloakState());

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            return $uncloakedDefenders->isEmpty()
                ? $fleetWrapper->getShipWrappers()
                : $uncloakedDefenders;
        } else {

            $spacecrafts = new ArrayCollection([$this->leader->get()->getId() => $this->leader]);

            $this->addDockedTo($spacecrafts);

            return $spacecrafts;
        }
    }

    /** @param Collection<int, ShipWrapperInterface> $spacecrafts */
    private function addDockedTo(Collection $spacecrafts): void
    {
        $dockedTo = $spacecrafts
            ->filter(fn (ShipWrapperInterface $wrapper) => $wrapper->getDockedToShipWrapper() !== null)
            ->map(fn (ShipWrapperInterface $wrapper) => $wrapper->getDockedToShipWrapper())
            ->filter(fn (ShipWrapperInterface $dockedTo) => !$dockedTo->get()->getUser()->isNpc());

        /** @var ShipWrapperInterface $wrapper */
        foreach ($dockedTo as $wrapper) {
            if (!$spacecrafts->contains($wrapper)) {
                $spacecrafts->set($wrapper->get()->getId(), $wrapper);
            }
        }
    }
}
