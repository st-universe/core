<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class RoundBasedBattleParty
{
    /** @var Collection<int, int> */
    private Collection $unUsedIds;

    public function __construct(
        private BattlePartyInterface $battleParty,
        private ShipRepositoryInterface $shipRepository
    ) {
        $this->unUsedIds = new ArrayCollection($battleParty->getActiveMembers(true)->getKeys());
    }

    public function get(): BattlePartyInterface
    {
        return $this->battleParty;
    }

    public function use(int $spacecraftId): void
    {
        $this->unUsedIds->removeElement($spacecraftId);
    }

    public function isDone(): bool
    {
        return $this->isUsed() || $this->getAllUnusedThatCanFire()->isEmpty();
    }

    private function isUsed(): bool
    {
        return $this->unUsedIds->isEmpty();
    }

    /** @return Collection<int, ShipWrapperInterface> */
    public function getAllUnusedThatCanFire(): Collection
    {
        return $this->get()
            ->getActiveMembers(true)
            ->filter(fn (ShipWrapperInterface $wrapper) => $this->unUsedIds->contains($wrapper->get()->getId()));
    }

    public function getRandomUnused(): ShipWrapperInterface
    {
        $allUnusedThatCanFire = $this->getAllUnusedThatCanFire();

        /** @var ShipWrapperInterface|null */
        $random = $allUnusedThatCanFire->get(array_rand($allUnusedThatCanFire->toArray()));
        if ($random === null) {
            throw new RuntimeException('isDone shoule be called first!');
        }

        $this->use($random->get()->getId());

        return $random;
    }

    public function saveActiveMembers(): void
    {
        foreach ($this->get()->getActiveMembers(false, false) as $wrapper) {

            $this->shipRepository->save($wrapper->get());
        }
    }
}
