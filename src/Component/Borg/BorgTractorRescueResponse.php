<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

final class BorgTractorRescueResponse
{
    public function __construct(
        private SpacecraftWrapperFactoryInterface $wrappers,
        private BattlePartyFactoryInterface $parties,
        private SpacecraftAttackCycleInterface $cycle,
        private DistributedMessageSenderInterface $messages,
        private FightLibInterface $fight,
        private BorgSphereAttackContext $context,
        private TholianWebUtilInterface $webs
    ) {}

    public function respond(BorgSphereEvent $event, Ship $cube, Ship $rescuer, InformationInterface $information): bool
    {
        if (!$this->isAvailable($event, $cube) || !$this->isTarget($cube, $rescuer)) {
            return false;
        }
        $source = $this->wrappers->wrapShip($cube);
        if (!$source->canFire()) {
            return false;
        }
        $this->context->beginAutomaticAttack($cube);
        try {
            $attackers = $this->attackers($source);
            if ($attackers === null) {
                return false;
            }
            $defenders = $this->defenders($cube, $this->wrappers->wrapShip($rescuer));
            if ($defenders === null) {
                return false;
            }
            $messages = $this->cycle->cycle($attackers, $defenders, SpacecraftAttackCauseEnum::BORG_TRACTOR_RESCUE_RESPONSE);
            $this->messages->distributeMessageCollection($messages, $cube->getUser()->getId(), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, sprintf('Kampf in Sektor %s', $cube->getSectorString()));
            $messages->getInformationDump()->dumpTo($information);
            return true;
        } finally {
            $this->context->clear();
        }
    }

    private function isAvailable(BorgSphereEvent $event, Ship $cube): bool
    {
        return $event->getShipId() === $cube->getId() && !$event->isFinished()
            && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && !$cube->getCondition()->isDestroyed() && !$cube->getCondition()->isDisabled()
            && !$cube->isWarped() && !$cube->isCloaked() && $cube->hasEnoughCrew();
    }

    private function isTarget(Ship $cube, Ship $rescuer): bool
    {
        return $cube->getLocation() === $rescuer->getLocation()
            && $cube->getUser()->getId() !== $rescuer->getUser()->getId()
            && $this->isAvailableRescuer($rescuer)
            && $this->sameWebSide($cube, $rescuer)
            && $this->fight->canAttackTarget($cube, $rescuer, true);
    }

    private function isAvailableRescuer(Ship $rescuer): bool
    {
        return !$rescuer->getCondition()->isDestroyed() && !$rescuer->getCondition()->isDisabled()
            && !$rescuer->isWarped() && !$rescuer->isCloaked() && !$rescuer->isRpgModuleInvisible()
            && !$rescuer->getUser()->isVacationRequestOldEnough()
            && !$rescuer->getRump()->isEscapePods() && !$rescuer->isBeingAssimilated();
    }

    private function attackers(SpacecraftWrapperInterface $source): ?BattlePartyInterface
    {
        $members = $this->parties->createAttackingBattleParty($source, false)->getActiveMembers(true)
            ->filter(fn (SpacecraftWrapperInterface $wrapper): bool =>
                $wrapper->get()->getUser()->getId() === $source->get()->getUser()->getId()
                && $this->context->allowsAutomaticAttackTarget($wrapper->get())
                && $this->sameWebSide($source->get(), $wrapper->get()));
        return $this->party($source, $members);
    }

    private function defenders(Ship $cube, SpacecraftWrapperInterface $rescuer): ?BattlePartyInterface
    {
        $members = $this->parties->createAttackedBattleParty($rescuer)->getActiveMembers()
            ->filter(fn (SpacecraftWrapperInterface $wrapper): bool => $this->sameWebSide($cube, $wrapper->get()));
        return $this->party($rescuer, $members);
    }

    /** @param Collection<int, SpacecraftWrapperInterface> $members */
    private function party(SpacecraftWrapperInterface $leader, Collection $members): ?BattlePartyInterface
    {
        if ($members->isEmpty()) {
            return null;
        }
        $ordered = new ArrayCollection([$leader->get()->getId() => $leader]);
        foreach ($members as $wrapper) {
            $ordered->set($wrapper->get()->getId(), $wrapper);
        }
        return $this->parties->createMixedBattleParty($ordered);
    }

    private function sameWebSide(Spacecraft $cube, Spacecraft $member): bool
    {
        return !$this->webs->isTargetOutsideFinishedTholianWeb($cube, $member)
            && !$this->webs->isTargetInsideFinishedTholianWeb($cube, $member);
    }
}
