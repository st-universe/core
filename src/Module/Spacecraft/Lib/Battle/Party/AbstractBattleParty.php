<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\User;

abstract class AbstractBattleParty implements BattlePartyInterface
{
    private bool $isStation;
    private User $user;

    /** @var Collection<int, covariant SpacecraftWrapperInterface> $members */
    private ?Collection $members = null;

    public function __construct(
        protected SpacecraftWrapperInterface $leader,
        protected StuRandom $stuRandom,
        private bool $isAttackingShieldsOnly = false
    ) {
        $this->isStation = $leader->get()->isStation();
        $this->user = $leader->get()->getUser();
    }

    /** 
     * @return Collection<int, covariant SpacecraftWrapperInterface> 
     */
    abstract protected function initMembers(): Collection;

    #[\Override]
    public function getUser(): User
    {
        return $this->user;
    }

    #[\Override]
    public function getLeader(): SpacecraftWrapperInterface
    {
        return $this->leader;
    }

    #[\Override]
    public function getActiveMembers(bool $canFire = false, bool $filterDisabled = true): Collection
    {
        if ($this->members === null) {
            $this->members = $this->initMembers();
        }

        return $this->members->filter(
            fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->getCondition()->isDestroyed()
                && (!$filterDisabled || !$wrapper->get()->getCondition()->isDisabled())
                && (!$canFire || $wrapper->canFire())
        );
    }

    #[\Override]
    public function getRandomActiveMember(): SpacecraftWrapperInterface
    {
        $activeMembers = $this->getActiveMembers();
        $randomActiveMember = $activeMembers->get($this->stuRandom->array_rand($activeMembers->toArray()));
        if ($randomActiveMember === null) {
            throw new RuntimeException('isDefeated should be called first');
        }

        return $randomActiveMember;
    }

    #[\Override]
    public function isDefeated(): bool
    {
        return $this->getActiveMembers()->isEmpty();
    }


    #[\Override]
    public function isStation(): bool
    {
        return $this->isStation;
    }

    #[\Override]
    public function isAttackingShieldsOnly(): bool
    {
        return $this->isAttackingShieldsOnly;
    }

    #[\Override]
    public function isActive(): bool
    {
        return $this->getActiveMembers()
            ->exists(fn(int $key, SpacecraftWrapperInterface $wrapper): bool => $wrapper->get()->hasEnoughCrew());
    }

    /**
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    protected function createSingleton(SpacecraftWrapperInterface $wrapper): Collection
    {
        return new ArrayCollection([$wrapper->get()->getId() => $wrapper]);
    }

    #[\Override]
    public function count(): int
    {
        return $this->getActiveMembers()->count();
    }

    #[\Override]
    public function getPrivateMessageType(): PrivateMessageFolderTypeEnum
    {
        return $this->isStation()
            ? PrivateMessageFolderTypeEnum::SPECIAL_STATION
            : PrivateMessageFolderTypeEnum::SPECIAL_SHIP;
    }
}
