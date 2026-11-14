<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Component\Borg\BorgCompanionDeparture;
use Stu\Component\Borg\BorgCompanionSupply;
use Stu\Component\Borg\BorgSphereBehavior;
use Stu\Component\Borg\BorgSphereCombat;
use Stu\Component\Borg\BorgSphereEnergy;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRegeneration;
use Stu\Component\Borg\BorgSphereTorpedoSupply;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BorgSphereTick implements ProcessTickHandlerInterface
{
    public function __construct(private BorgSphereRepositoryInterface $events, private ShipRepositoryInterface $ships, private BorgSphereRegeneration $regeneration, private BorgSphereBehavior $behavior, private BorgSphereEnergy $energy, private SpacecraftRemoverInterface $remover, private StuTime $time, private BorgSphereCombat $combat, private BorgSphereTorpedoSupply $torpedoes, private ?BorgCompanionSupply $companions = null, private ?BorgCompanionDeparture $departure = null) {}

    #[Override]
    public function work(): void
    {
        $now = $this->time->time();
        foreach ($this->events->getDue($now) as $event) {
            $this->workEvent($event, $now);
        }
    }

    private function workEvent(BorgSphereEvent $event, int $now): void
    {
        $ship = $event->getShipId() === null ? null : $this->ships->find($event->getShipId());
        if ($ship === null || $ship->getCondition()->isDestroyed()) {
            $event->setPhase(BorgSpherePhaseEnum::DESTROYED)->setShipId(null);
            return;
        }
        $this->combat->withLockedEvent($ship, $event, fn () => $this->advanceEvent($event, $ship, $now));
    }

    private function advanceEvent(BorgSphereEvent $event, Ship $ship, int $now): void
    {
        $configuration = $event->getConfiguration();
        if (!$configuration->isActive($now)) {
            $this->escape($event, $ship);
            return;
        }
        $interval = $configuration->getSetting('tick_seconds');
        $steps = max(1, intdiv($now - $event->getNextTickAt(), $interval) + 1);
        $event->setNextTickAt($event->getNextTickAt() + $steps * $interval);
        $this->regeneration->regenerate($event, $ship, $steps);
        if ($event->getFirstDisabledAt() !== null && $event->getTranswarpProgress() >= $configuration->getSetting('transwarp_escape_bp')) {
            $this->escape($event, $ship);
            return;
        }
        $this->energy->recharge($ship);
        $this->torpedoes->refill($ship);
        $this->companions?->refill($ship);
        $this->behavior->work($event, $ship);
        $this->events->save($event);
    }

    private function escape(BorgSphereEvent $event, Ship $ship): void
    {
        $event->setTranswarpProgress($event->getConfiguration()->getSetting('transwarp_escape_bp'))->setPhase(BorgSpherePhaseEnum::ESCAPED)->setShipId(null);
        $this->departure?->depart($ship);
        $this->remover->remove($ship, true);
        $this->events->save($event);
    }
}
