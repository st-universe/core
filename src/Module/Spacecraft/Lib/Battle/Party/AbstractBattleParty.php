<?php

namespace Stu\Module\Spacecraft\Lib\Battle\Party;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\UserInterface;

abstract class AbstractBattleParty implements BattlePartyInterface
{
    private bool $isStation;
    private UserInterface $user;

    /** @var Collection<int, covariant SpacecraftWrapperInterface> $members */
    private ?Collection $members = null;

    public function __construct(
        protected SpacecraftWrapperInterface $leader,
        private bool $isAttackingShieldsOnly = false
    ) {
        $this->isStation = $leader->get()->isStation();
        $this->user = $leader->get()->getUser();
    }

    /** 
     * @return Collection<int, covariant SpacecraftWrapperInterface> 
     */
    abstract protected function initMembers(): Collection;

    #[Override]
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function getLeader(): SpacecraftWrapperInterface
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
            fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->getCondition()->isDestroyed()
                && (!$filterDisabled || !$wrapper->get()->getCondition()->isDisabled())
                && (!$canFire || $wrapper->canFire())
        );
    }

    #[Override]
    public function getRandomActiveMember(): SpacecraftWrapperInterface
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
    public function isStation(): bool
    {
        return $this->isStation;
    }

    #[Override]
    public function isAttackingShieldsOnly(): bool
    {
        return $this->isAttackingShieldsOnly;
    }

    /**
     * @return Collection<int, SpacecraftWrapperInterface>
     */
    protected function createSingleton(SpacecraftWrapperInterface $wrapper): Collection
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
        return $this->isStation()
            ? PrivateMessageFolderTypeEnum::SPECIAL_STATION
            : PrivateMessageFolderTypeEnum::SPECIAL_SHIP;
    }
}
