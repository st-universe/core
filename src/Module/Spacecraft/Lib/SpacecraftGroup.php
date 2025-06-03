<?php

namespace Stu\Module\Spacecraft\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

class SpacecraftGroup implements SpacecraftGroupInterface
{
    /** @var Collection<int, SpacecraftWrapperInterface> */
    private Collection $spacecraftWrappers;

    public function __construct(
        private String $name,
        private ?UserInterface $user
    ) {
        $this->spacecraftWrappers = new ArrayCollection();
    }

    #[Override]
    public function addSpacecraftWrapper(SpacecraftWrapperInterface $wrapper): void
    {
        $this->spacecraftWrappers->add($wrapper);
    }

    #[Override]
    public function getWrappers(): Collection
    {
        return $this->spacecraftWrappers;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /** 
     * @param Collection<int, covariant SpacecraftInterface> $spacecrafts 
     * 
     * @return Collection<int, SpacecraftInterface>
     */
    public static function sortSpacecraftCollection(Collection $spacecrafts): Collection
    {
        $spacecraftArray = $spacecrafts->toArray();

        usort(
            $spacecraftArray,
            function (SpacecraftInterface $a, SpacecraftInterface $b): int {
                $fleetA = $a instanceof ShipInterface ? $a->getFleet() : null;
                $fleetB = $b instanceof ShipInterface ? $b->getFleet() : null;

                $fleetASort = $fleetA !== null ? $fleetA->getSort() : 0;
                $fleetBSort = $fleetB !== null ? $fleetB->getSort() : 0;
                if ($fleetBSort === $fleetASort) {
                    $fleetAid = $fleetA !== null ? $fleetA->getId() : 0;
                    $fleetBid = $fleetB !== null ? $fleetB->getId() : 0;
                    if ($fleetBid === $fleetAid) {
                        $aIsFleetLeader = $a instanceof ShipInterface && $a->isFleetLeader();
                        $bIsFleetLeader = $b instanceof ShipInterface && $b->isFleetLeader();
                        if ($bIsFleetLeader === $aIsFleetLeader) {
                            $catA = $a->getRump()->getCategoryId();
                            $catB = $b->getRump()->getCategoryId();
                            if ($catB === $catA) {
                                $roleA = $a->getRump()->getRoleId();
                                $roleB = $b->getRump()->getRoleId();
                                if ($roleB === $roleA) {
                                    if ($b->getRumpId() === $a->getRumpId()) {
                                        return $a->getName() <=> $b->getName();
                                    }

                                    return $b->getRumpId() <=> $a->getRumpId();
                                }

                                return $roleB <=> $roleA;
                            }
                            return $catB <=> $catA;
                        }
                        return $bIsFleetLeader <=> $aIsFleetLeader;
                    }
                    return $fleetBid <=> $fleetAid;
                }
                return $fleetBSort <=> $fleetASort;
            }
        );

        return new ArrayCollection($spacecraftArray);
    }
}
