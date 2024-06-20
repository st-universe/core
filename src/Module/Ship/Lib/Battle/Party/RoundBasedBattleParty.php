<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class RoundBasedBattleParty
{
    /** @var array<int> */
    private array $unUsedIds;

    public function __construct(
        private BattlePartyInterface $battleParty,
        private ShipRepositoryInterface $shipRepository
    ) {
        $this->unUsedIds = $battleParty->getActiveMembers()->getKeys();
    }

    public function get(): BattlePartyInterface
    {
        return $this->battleParty;
    }

    public function use(int $spacecraftId): void
    {
        unset($this->unUsedIds[$spacecraftId]);
    }

    public function isDone(): bool
    {
        return $this->isUsed() || $this->getAllUnusedThatCanFire()->isEmpty();
    }

    private function isUsed(): bool
    {
        return empty($this->unUsedIds);
    }

    /** @return Collection<int, ShipWrapperInterface> */
    public function getAllUnusedThatCanFire(): Collection
    {
        return $this->get()
            ->getActiveMembers(true)
            ->filter(fn (ShipWrapperInterface $wrapper) => in_array($wrapper->get()->getId(), $this->unUsedIds));
    }

    public function getRandomUnused(): ShipWrapperInterface
    {
        $allUnusedThatCanFire = $this->getAllUnusedThatCanFire();

        /** @var ShipWrapperInterface|null */
        $random = $allUnusedThatCanFire->get(array_rand($allUnusedThatCanFire->toArray()));
        if ($random === null) {
            throw new RuntimeException('isDone shoule be called first!');
        }

        unset($this->unUsedIds[$random->get()->getId()]);

        return $random;
    }

    public function saveActiveMembers(): void
    {
        foreach ($this->get()->getActiveMembers(false, false) as $wrapper) {

            $this->shipRepository->save($wrapper->get());
        }
    }
}
