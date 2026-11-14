<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgWebReaction
{
    public function __construct(
        private BorgSphereRepositoryInterface $events,
        private SpacecraftWrapperFactoryInterface $wrappers,
        private BattlePartyFactoryInterface $parties,
        private SpacecraftAttackCycleInterface $cycle,
        private DistributedMessageSenderInterface $messages,
        private StuRandom $random,
        private StuTime $time
    ) {}

    public function react(TholianWeb $web, InformationInterface $information): void
    {
        foreach ($web->getCapturedSpacecrafts()->toArray() as $ship) {
            if ($web->getCondition()->isDestroyed()) {
                return;
            }
            if (!$ship instanceof Ship || !$this->canFire($ship, $web)) {
                continue;
            }
            $event = $this->events->getByShip($ship);
            if ($event === null) {
                continue;
            }
            $this->events->lockEvent($event);
            if ($this->isAvailable($event, $ship, $web)) {
                $this->fire($event, $ship, $web, $information);
            }
        }
    }

    private function canFire(Ship $ship, TholianWeb $web): bool
    {
        return $ship->getHoldingWeb() === $web && $ship->getLocation() === $web->getLocation()
            && !$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed()
            && !$ship->isWarped() && !$ship->isCloaked() && $ship->hasEnoughCrew();
    }

    private function isAvailable(BorgSphereEvent $event, Ship $ship, TholianWeb $web): bool
    {
        return $event->getShipId() === $ship->getId()
            && $event->getConfiguration()->getUser()->getId() === $ship->getUser()->getId()
            && !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && $event->getConfiguration()->isActive($this->time->time()) && $this->canFire($ship, $web);
    }

    private function fire(BorgSphereEvent $event, Ship $ship, TholianWeb $web, InformationInterface $information): void
    {
        $source = $this->wrappers->wrapShip($ship);
        if (!$source->canFire() || $this->random->rand(1, 100) > ($event->getConfiguration()->getSettings()['web_retaliation_chance_percent'] ?? 25)) {
            return;
        }
        $messages = $this->cycle->cycle(
            $this->parties->createSingletonBattleParty($source),
            $this->parties->createSingletonBattleParty($this->wrappers->wrapSpacecraft($web)),
            SpacecraftAttackCauseEnum::BORG_WEB_REACTION
        );
        $this->messages->distributeMessageCollection($messages, $ship->getUser()->getId(), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, sprintf('Kampf am Energienetz in Sektor %s', $ship->getSectorString()));
        $messages->getInformationDump()->dumpTo($information);
    }
}
