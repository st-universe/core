<?php

namespace Stu\Module\Ship\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\UserInterface;

abstract class AbstractBattleParty implements BattlePartyInterface
{
    private bool $isBase;
    private UserInterface $user;

    /** @var Collection<int, ShipWrapperInterface> $members */
    private ?Collection $members = null;

    public function __construct(
        protected ShipWrapperInterface $leader
    ) {
        $this->isBase = $leader->get()->isBase();
        $this->user = $leader->get()->getUser();
    }

    /** @return Collection<int, ShipWrapperInterface> */
    abstract protected function initMembers(): Collection;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function getLeader(): ShipWrapperInterface
    {
        return $this->leader;
    }

    #[Override]
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection
    {
        if ($this->members === null) {
            $this->members = $this->initMembers();
        }

        return $this->members->filter(
            fn (ShipWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed()
                && (!$filterDisabled || !$wrapper->get()->isDisabled())
                && (!$canFire || $wrapper->canFire())
        );
    }

    #[Override]
    public function getRandomActiveMember(): ShipWrapperInterface
    {
        $activeMembers = $this->getActiveMembers();
        $randomActiveMember = $activeMembers->get(array_rand($activeMembers->toArray()));
        if ($randomActiveMember === null) {
            throw new RuntimeException('isDefeated should be called first');
        }

        return $randomActiveMember;
    }

    #[Override]
    public function isDefeated(): bool
    {
        return $this->getActiveMembers()->isEmpty();
    }


    #[Override]
    public function isBase(): bool
    {
        return $this->isBase;
    }

    /**
     * @return Collection<int, ShipWrapperInterface>
     */
    protected function createSingleton(ShipWrapperInterface $wrapper): Collection
    {
        return new ArrayCollection([$wrapper->get()->getId() => $wrapper]);
    }

    #[Override]
    public function count(): int
    {
        return $this->getActiveMembers()->count();
    }

    #[Override]
    public function getPrivateMessageType(): PrivateMessageFolderTypeEnum
    {
        return $this->isBase()
            ? PrivateMessageFolderTypeEnum::SPECIAL_STATION
            : PrivateMessageFolderTypeEnum::SPECIAL_SHIP;
    }
}
